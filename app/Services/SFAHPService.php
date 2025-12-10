<?php

namespace App\Services;

use App\Models\AhpMatrix;
use App\Models\Expert;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Service untuk Spherical Fuzzy AHP (SF-AHP)
 * Implementasi berdasarkan Gundogdu & Kahraman (2019)
 */
class SFAHPService
{
    /**
     * Mapping linguistik → spherical fuzzy (sesuai Table 1 — Gundogdu & Kahraman 2019)
     * Return: [mu, nu, pi]
     */
    public function fuzzyScale(string $code): array
    {
        $code = strtoupper(trim($code));

        return match ($code) {
            'AMI' => [0.9, 0.1, 0.0],
            'VHI' => [0.8, 0.2, 0.1],
            'HI'  => [0.7, 0.3, 0.2],
            'SMI' => [0.6, 0.4, 0.3],
            'EI'  => [0.5, 0.4, 0.4],
            'SLI' => [0.4, 0.6, 0.3],
            'LI'  => [0.3, 0.7, 0.2],
            'VLI' => [0.2, 0.8, 0.1],
            'ALI' => [0.1, 0.9, 0.0],
            default => [0.5, 0.4, 0.4],
        };
    }

    /**
     * Reciprocal spherical fuzzy — determined by linguistics symmetric pair.
     * We return tuple for reciprocal label (mirror in the table).
     */
    public function reciprocalByLabel(string $label): array
    {
        $label = strtoupper(trim($label));

        $reverseLabel = match ($label) {
            'AMI' => 'ALI',
            'VHI' => 'VLI',
            'HI'  => 'LI',
            'SMI' => 'SLI',
            'EI'  => 'EI',
            'SLI' => 'SMI',
            'LI'  => 'HI',
            'VLI' => 'VHI',
            'ALI' => 'AMI',
            default => 'EI',
        };

        return $this->fuzzyScale($reverseLabel);
    }

    /**
     * Simpan fuzzy + reciprocal otomatis ke tabel ahp_matrices
     *
     * - menyimpan fuzzy tuple sesuai label
     * - menyimpan reciprocal berdasarkan pasangan linguistik (label lawan)
     */
    public function saveFuzzy(int $expertId, int $k1, int $k2, string $label): void
    {
        $label = strtoupper(trim($label));
        $f = $this->fuzzyScale($label);

        $data = [
            'mu' => $f[0],
            'nu' => $f[1],
            'pi' => $f[2],
        ];

        // Simpan label jika kolom ada
        try {
            $cols = Schema::getColumnListing((new AhpMatrix())->getTable());
            if (in_array('label', $cols)) $data['label'] = $label;
        } catch (\Throwable $e) {
            $cols = [];
        }

        AhpMatrix::updateOrCreate(
            ['expert_id' => $expertId, 'kriteria_1_id' => $k1, 'kriteria_2_id' => $k2],
            $data
        );

        // reciprocal (nilai dari label lawan)
        if ($k1 !== $k2) {
            $r = $this->reciprocalByLabel($label);

            $rdata = [
                'mu' => $r[0],
                'nu' => $r[1],
                'pi' => $r[2],
            ];

            try {
                if (in_array('label', $cols ?? [])) {
                    $rdata['label'] = ($label === 'EI') ? 'EI' : (strtoupper(trim($label)) . '_REC');
                }
            } catch (\Throwable $e) {}

            AhpMatrix::updateOrCreate(
                ['expert_id' => $expertId, 'kriteria_1_id' => $k2, 'kriteria_2_id' => $k1],
                $rdata
            );
        }
    }

    /**
     * SWGM Aggregation — Multi-expert spherical fuzzy geometric mean aggregation
     *
     * items: Collection of criteria items (must have id)
     * overrideWeights: optional array of expert weights (normalized externally)
     *
     * Returns:
     *  - matrix: aggregated spherical fuzzy matrix A[i][j] = ['mu','nu','pi']
     *  - idIndex: array of item ids in order
     *  - items: original items collection
     */
    public function swgmAggregate(Collection $items, ?array $overrideWeights = null): array
    {
        $items = $items->values();
        $n = $items->count();

        if ($n == 0) {
            return ['matrix' => [], 'idIndex' => [], 'items' => $items];
        }

        $idIndex = $items->pluck('id')->toArray();

        $experts = Expert::all();
        if ($experts->count() == 0) {
            $experts = collect([(object)['id' => 0, 'weight' => 1]]);
        }

        // normalize expert weights
        $weights = $experts->pluck('weight')->map(fn($w) => floatval($w))->toArray();
        if ($overrideWeights && count($overrideWeights) == count($weights)) {
            $weights = $overrideWeights;
        }
        $sum = array_sum($weights);
        if ($sum == 0) {
            $weights = array_fill(0, count($weights), 1 / max(1, count($weights)));
        } else {
            foreach ($weights as $k => $w) $weights[$k] = $w / $sum;
        }

        // init aggregated spherical matrix with identity (1,0,0)
        $A = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $A[$i][$j] = ['mu' => 1.0, 'nu' => 0.0, 'pi' => 0.0];
            }
        }

        // iterate each expert and aggregate using SWGM approach adapted for spherical fuzzy
        foreach ($experts->values() as $eIndex => $expert) {
            $w = $weights[$eIndex];

            // fetch expert rows relevant to our idIndex (grouped)
            $rows = AhpMatrix::where('expert_id', $expert->id)
                ->whereIn('kriteria_1_id', $idIndex)
                ->whereIn('kriteria_2_id', $idIndex)
                ->get()
                ->groupBy('kriteria_1_id');

            for ($i = 0; $i < $n; $i++) {
                for ($j = 0; $j < $n; $j++) {
                    $id_i = $idIndex[$i];
                    $id_j = $idIndex[$j];

                    $entry = null;
                    if (isset($rows[$id_i])) {
                        $entry = $rows[$id_i]->firstWhere('kriteria_2_id', $id_j);
                    }

                    // if direct entry not found, try reciprocal stored opposite cell
                    if (!$entry) {
                        $op = AhpMatrix::where('expert_id', $expert->id)
                            ->where('kriteria_1_id', $id_j)
                            ->where('kriteria_2_id', $id_i)
                            ->first();
                        if ($op) {
                            // if reciprocal stored as opposite label, use op values swapped according to reciprocal rule:
                            // we assume op represents the reciprocal already (saved via saveFuzzy), so use op's mu/nu/pi as-is
                            $mu = floatval($op->mu ?? 1.0);
                            $nu = floatval($op->nu ?? 0.0);
                            $pi = floatval($op->pi ?? 0.0);
                        } else {
                            $mu = 1.0; $nu = 0.0; $pi = 0.0;
                        }
                    } else {
                        $mu = floatval($entry->mu ?? 1.0);
                        $nu = floatval($entry->nu ?? 0.0);
                        $pi = floatval($entry->pi ?? 0.0);
                    }

                    // SWGM aggregation for spherical fuzzy:
                    // for μ: multiplicative power-weighted geometric mean
                    $A[$i][$j]['mu'] *= pow(max(1e-12, $mu), $w);

                    // for ν and π: use dual multiplicative formulation converted to keep values in [0,1]
                    // we combine by: 1 - Π_k (1 - val_k)^{w_k}  — a probabilistic OR-like aggregation
                    // initialize in loop: we store running complement product by converting current to complement
                    // But since we hold direct value, implement incremental transform:
                    $A[$i][$j]['nu'] = 1 - ((1 - $A[$i][$j]['nu']) * pow(max(1e-12, 1 - $nu), $w));
                    $A[$i][$j]['pi'] = 1 - ((1 - $A[$i][$j]['pi']) * pow(max(1e-12, 1 - $pi), $w));
                }
            }
        }

        // After aggregating, ensure spherical constraint mu^2 + nu^2 + pi^2 <= 1.
        foreach ($A as $i => $row) {
            foreach ($row as $j => $cell) {
                $norm = ($cell['mu'] ** 2) + ($cell['nu'] ** 2) + ($cell['pi'] ** 2);
                if ($norm > 1.0) {
                    $scale = sqrt(1.0 / $norm);
                    $A[$i][$j]['mu'] *= $scale;
                    $A[$i][$j]['nu'] *= $scale;
                    $A[$i][$j]['pi'] *= $scale;
                }
            }
        }

        return [
            'matrix' => $A,
            'idIndex' => $idIndex,
            'items' => $items
        ];
    }

    /**
     * Spherical Fuzzy → Crisp Score (Compatible with VIKOR)
     *
     * Uses ideal (1,0,0) and anti-ideal (0,1,1) distances:
     * S = d_minus / (d_plus + d_minus)
     */
    public function scoreFunction(array $t): float
    {
        $mu = $t['mu'] ?? 1.0;
        $nu = $t['nu'] ?? 0.0;
        $pi = $t['pi'] ?? 0.0;

        // distance to ideal (1,0,0)
        $d_plus = sqrt(pow($mu - 1, 2) + pow($nu - 0, 2) + pow($pi - 0, 2));

        // distance to anti-ideal (0,1,1)
        $d_minus = sqrt(pow($mu - 0, 2) + pow($nu - 1, 2) + pow($pi - 1, 2));

        if (($d_plus + $d_minus) == 0) return 0.5; // fallback balanced

        return $d_minus / ($d_plus + $d_minus);
    }


    /**
     * Hitung bobot dari spherical fuzzy matrix
     * Mengembalikan crisp matrix, weights (eigenvector approx by row averages), lambda_max, CI, CR
     */
    public function computeWeightsFromSfMatrix(array $sfMatrix): array
    {
        $n = count($sfMatrix);
        if ($n == 0) {
            return [
                'crisp_matrix' => [],
                'weights' => [],
                'lambda_max' => 0,
                'CI' => 0,
                'CR' => 0,
                'normalisasi' => []
            ];
        }

        // build crisp matrix A_ij = score(sfMatrix[i][j])
        $A = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $A[$i][$j] = $this->scoreFunction($sfMatrix[$i][$j]);
            }
        }

        // column sums
        $colsum = [];
        for ($j = 0; $j < $n; $j++) {
            $colsum[$j] = 0.0;
            for ($i = 0; $i < $n; $i++) {
                $colsum[$j] += ($A[$i][$j] ?? 0.0);
            }
            if (abs($colsum[$j]) < 1e-12) $colsum[$j] = 1e-12;
        }

        // normalize (divide each column by its sum)
        $N = [];
        for ($i = 0; $i < $n; $i++) {
            $N[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $N[$i][$j] = $A[$i][$j] / $colsum[$j];
            }
        }

        // weights: row averages
        $EV = [];
        for ($i = 0; $i < $n; $i++) {
            $EV[$i] = array_sum($N[$i]) / $n;
        }

        // compute lambda_max approximation
        $lambda = [];
        for ($i = 0; $i < $n; $i++) {
            $rowSum = 0.0;
            for ($j = 0; $j < $n; $j++) {
                $rowSum += $A[$i][$j] * ($EV[$j] ?? 0.0);
            }
            $lambda[$i] = ($EV[$i] != 0.0) ? ($rowSum / $EV[$i]) : 0.0;
        }
        $lambda_max = array_sum($lambda) / $n;

        // CI & CR
        $CI = ($lambda_max - $n) / max(1, ($n - 1));
        $RI_TABLE = [0,0,0,0.58,0.90,1.12,1.24,1.32,1.41,1.45,1.51];
        $RI = $RI_TABLE[$n] ?? 1.51;
        $CR = ($RI == 0.0) ? 0.0 : ($CI / $RI);

        return [
            'crisp_matrix' => $A,
            'weights' => $EV,
            'lambda_max' => $lambda_max,
            'CI' => $CI,
            'CR' => $CR,
            'normalisasi' => $N
        ];
    }

    /**
     * Agregasi + hitung bobot (helper end-to-end)
     */
    public function aggregateAndCompute(Collection $items): array
    {
        $agg = $this->swgmAggregate($items);
        $sf = $agg['matrix'];
        $comp = $this->computeWeightsFromSfMatrix($sf);

        return [
            'items' => $agg['items'],
            'idIndex' => $agg['idIndex'],
            'sf_matrix' => $sf,
            'crisp_matrix' => $comp['crisp_matrix'],
            'weights' => $comp['weights'],
            'lambda_max' => $comp['lambda_max'],
            'CI' => $comp['CI'],
            'CR' => $comp['CR']
        ];
    }

    /**
     * Simpan bobot kriteria (urut sesuai items collection)
     */
    public function saveWeightsToKriteria(Collection|array $items, array $weights): void
    {
        foreach ($items as $i => $item) {
            \App\Models\Kriteria::where('id', $item->id)
                ->update(['bobot' => $weights[$i] ?? 0]);
        }
    }

    /**
     * Simpan bobot sub-kriteria (local & global maps)
     */
    public function saveSubCriteriaWeights(array $localMap, array $globalMap): void
    {
        foreach ($localMap as $id => $localWeight) {
            \App\Models\Kriteria::where('id', $id)->update(['bobot' => $localWeight]);
        }
        foreach ($globalMap as $id => $globalWeight) {
            \App\Models\Kriteria::where('id', $id)->update(['bobot_global' => $globalWeight]);
        }
    }
}
