<?php
namespace App\Services;

use App\Models\AhpMatrix;
use App\Models\Expert;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SFAHPService
{
    /**
     * Mapping linguistik → spherical fuzzy (Gundogdu & Kahraman 2019)
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
            default => [0.5, 0.4, 0.4]
        };
    }

    /**
     * Reciprocal spherical fuzzy → (mu'=pi, nu'=nu, pi'=mu)
     */
    public function reciprocal(array $f): array
    {
        return [
            $f[1], // mu'
            $f[0], // nu'
            $f[2]  // pi'
        ];
    }

    /**
     * Simpan fuzzy + reciprocal otomatis
     */
    public function saveFuzzy(int $expertId, int $k1, int $k2, string $label): void
    {
        $label = strtoupper(trim($label));
        $f = $this->fuzzyScale($label);

        $data = [
            'mu' => $f[1],
            'nu' => $f[0],
            'pi' => $f[2],
        ];

        // Simpan label jika kolom tersedia
        try {
            $cols = Schema::getColumnListing((new AhpMatrix())->getTable());
            if (in_array('label', $cols)) $data['label'] = $label;
        } catch (\Throwable $e) {}

        AhpMatrix::updateOrCreate(
            ['expert_id'=>$expertId,'kriteria_1_id'=>$k1,'kriteria_2_id'=>$k2],
            $data
        );

        /**
         * SIMPAN RECIPROCAL
         */
        if ($k1 != $k2) {
            $r = $this->reciprocal($f);

            $rdata = [
                'mu' => $r[0],
                'nu' => $r[1],
                'pi' => $r[2],
            ];

            try {
                if (in_array('label', $cols ?? [])) {
                    $rdata['label'] = $label . '_REC';
                }
            } catch (\Throwable $e) {}

            AhpMatrix::updateOrCreate(
                ['expert_id'=>$expertId,'kriteria_1_id'=>$k2,'kriteria_2_id'=>$k1],
                $rdata
            );
        }
    }

    /**
     * SWGM Aggregation — Multi pakar
     */
    public function swgmAggregate(Collection $items, ?array $overrideWeights = null): array
    {
        $items = $items->values();
        $n = $items->count();

        if ($n == 0) {
            return ['matrix'=>[], 'idIndex'=>[], 'items'=>$items];
        }

        $idIndex = $items->pluck('id')->toArray();

        $experts = Expert::all();
        if ($experts->count() == 0) {
            $experts = collect([(object)['id'=>0,'weight'=>1]]);
        }

        // expert weights
        $weights = $experts->pluck('weight')->map(fn($w)=>floatval($w))->toArray();
        if ($overrideWeights && count($overrideWeights) == count($weights)) {
            $weights = $overrideWeights;
        }
        $sum = array_sum($weights);
        if ($sum == 0) {
            $weights = array_fill(0, count($weights), 1 / max(1,count($weights)));
        } else {
            foreach ($weights as $k => $w) $weights[$k] = $w / $sum;
        }

        // init aggregated spherical matrix
        $A = [];
        for ($i=0;$i<$n;$i++){
            for ($j=0;$j<$n;$j++){
                $A[$i][$j] = ['mu'=>1.0,'nu'=>0.0,'pi'=>0.0];
            }
        }

        // iterate each expert
        foreach ($experts->values() as $eIndex => $expert) {
            $w = $weights[$eIndex];

            $rows = AhpMatrix::where('expert_id',$expert->id)
                ->whereIn('kriteria_1_id',$idIndex)
                ->whereIn('kriteria_2_id',$idIndex)
                ->get()
                ->groupBy('kriteria_1_id');

            for ($i=0;$i<$n;$i++){
                for ($j=0;$j<$n;$j++){
                    $id_i = $idIndex[$i];
                    $id_j = $idIndex[$j];

                    $entry = null;
                    if (isset($rows[$id_i])) {
                        $entry = $rows[$id_i]->firstWhere('kriteria_2_id',$id_j);
                    }

                    if ($entry) {
                        $mu = floatval($entry->mu ?? 1);
                        $nu = floatval($entry->nu ?? 0);
                        $pi = floatval($entry->pi ?? 0);
                    } else {
                        // try reciprocal
                        $op = AhpMatrix::where('expert_id',$expert->id)
                            ->where('kriteria_1_id',$id_j)
                            ->where('kriteria_2_id',$id_i)
                            ->first();

                        if ($op) {
                            $mu = floatval($op->pi);
                            $nu = floatval($op->nu);
                            $pi = floatval($op->mu);
                        } else {
                            $mu=1; $nu=0; $pi=0;
                        }
                    }

                    $A[$i][$j]['mu'] *= pow(max(1e-12,$mu), $w);
                    $A[$i][$j]['nu'] *= pow(max(1e-12,$nu), $w);
                    $A[$i][$j]['pi'] *= pow(max(1e-12,$pi), $w);
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
     * Spherical Score Function: S = μ - ν - π
     */
    public function scoreFunction(array $t): float
    {
        return ($t['mu'] ?? 1) - ($t['nu'] ?? 0) - ($t['pi'] ?? 0);
    }

    /**
     * Hitung bobot dari spherical fuzzy matrix
     */
    public function computeWeightsFromSfMatrix(array $sfMatrix): array
    {
        $n = count($sfMatrix);
        if ($n == 0) {
            return [
                'crisp_matrix'=>[],
                'weights'=>[],
                'lambda_max'=>0,
                'CI'=>0,
                'CR'=>0,
                'normalisasi'=>[]
            ];
        }

        // build crisp matrix (score matrix)
        $A = [];
        for ($i=0;$i<$n;$i++){
            for ($j=0;$j<$n;$j++){
                $A[$i][$j] = $this->scoreFunction($sfMatrix[$i][$j]);
            }
        }

        // sum columns
        $colsum = [];
        for ($j=0;$j<$n;$j++){
            $colsum[$j] = array_sum(array_column($A,$j));
            if (abs($colsum[$j]) < 1e-12) $colsum[$j] = 1e-12;
        }

        // normalize
        $N = [];
        for ($i=0;$i<$n;$i++){
            for ($j=0;$j<$n;$j++){
                $N[$i][$j] = $A[$i][$j] / $colsum[$j];
            }
        }

        // eigenvector (row averages)
        $EV = [];
        for ($i=0;$i<$n;$i++){
            $EV[$i] = array_sum($N[$i]) / $n;
        }

        // λ-max
        $lambda = [];
        for ($i=0;$i<$n;$i++){
            $rowSum = 0;
            for ($j=0;$j<$n;$j++){
                $rowSum += $A[$i][$j] * ($EV[$j] ?? 0);
            }
            $lambda[$i] = ($EV[$i] != 0) ? $rowSum / $EV[$i] : 0;
        }
        $lambda_max = array_sum($lambda) / $n;

        // CI & CR
        $CI = ($lambda_max - $n) / max(1,($n-1));
        $RI_TABLE = [0,0,0,0.58,0.90,1.12,1.24,1.32,1.41,1.45,1.51];
        $RI = $RI_TABLE[$n] ?? 1.51;
        $CR = ($RI == 0) ? 0 : $CI / $RI;

        return [
            'crisp_matrix'=>$A,
            'weights'=>$EV,
            'lambda_max'=>$lambda_max,
            'CI'=>$CI,
            'CR'=>$CR,
            'normalisasi'=>$N
        ];
    }

    /**
     * Agregasi + hitung bobot
     */
    public function aggregateAndCompute(Collection $items): array
    {
        $agg = $this->swgmAggregate($items);
        $sf = $agg['matrix'];
        $comp = $this->computeWeightsFromSfMatrix($sf);

        return [
            'items'         => $agg['items'],
            'idIndex'       => $agg['idIndex'],
            'sf_matrix'     => $sf,
            'crisp_matrix'  => $comp['crisp_matrix'],
            'weights'       => $comp['weights'],
            'lambda_max'    => $comp['lambda_max'],
            'CI'            => $comp['CI'],
            'CR'            => $comp['CR']
        ];
    }

    /**
     * Simpan bobot kriteria
     */
    public function saveWeightsToKriteria(Collection|array $items, array $weights): void
    {
        foreach ($items as $i => $item) {
            \App\Models\Kriteria::where('id',$item->id)
                ->update(['bobot'=>$weights[$i] ?? 0]);
        }
    }

    /**
     * Simpan bobot sub-kriteria
     */
    public function saveSubCriteriaWeights(array $localMap, array $globalMap): void
    {
        foreach ($localMap as $id => $localWeight) {
            \App\Models\Kriteria::where('id',$id)->update(['bobot'=>$localWeight]);
        }
        foreach ($globalMap as $id => $globalWeight) {
            \App\Models\Kriteria::where('id',$id)->update(['bobot_global'=>$globalWeight]);
        }
    }
}
