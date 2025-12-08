<?php
namespace App\Services;

use App\Models\Expert;
use App\Models\AhpMatrix;
use Illuminate\Support\Collection;

class AHPMultiExpertService
{
   
    public function aggregateMatricesForItems(Collection $items, array $extraExpertWeights = null)
    {
        $items = $items->values();
        $n = $items->count();
        if ($n == 0) return null;

        $experts = Expert::all();
        if ($experts->count() == 0) return null;

        // weights
        $weights = $experts->map(fn($e)=>(float)$e->weight)->toArray();
        $sum = array_sum($weights);
        if ($sum == 0) $weights = array_fill(0, $experts->count(), 1 / $experts->count());
        else $weights = array_map(fn($w)=> $w/$sum, $weights);

        if ($extraExpertWeights && count($extraExpertWeights) == $experts->count()) {
            $weights = $extraExpertWeights;
        }

        $idIndex = $items->pluck('id')->toArray();
        $A = array_fill(0, $n, array_fill(0, $n, 1.0));

        foreach ($experts->values() as $ek => $expert) {
            $mats = AhpMatrix::where('expert_id', $expert->id)
                    ->whereIn('kriteria_1_id', $idIndex)
                    ->whereIn('kriteria_2_id', $idIndex)
                    ->get();

            $lookup = [];
            foreach ($mats as $r) {
                $lookup[$r->kriteria_1_id][$r->kriteria_2_id] = (float)$r->nilai_perbandingan;
            }

            for ($i=0; $i<$n; $i++) {
                for ($j=0; $j<$n; $j++) {
                    $id_i = $idIndex[$i];
                    $id_j = $idIndex[$j];
                    $val = 1.0;
                    if (isset($lookup[$id_i][$id_j])) $val = (float)$lookup[$id_i][$id_j];
                    elseif ($id_i != $id_j && isset($lookup[$id_j][$id_i])) $val = 1 / (float)$lookup[$id_j][$id_i];
                    // SWGM (geometric mean with weights): multiply val^w_k
                    $A[$i][$j] *= pow($val, $weights[$ek]);
                }
            }
        }

        // ensure reciprocity
        for ($i=0; $i<$n; $i++) {
            for ($j=0; $j<$n; $j++) {
                if ($i == $j) { $A[$i][$j] = 1.0; continue; }
                $A[$j][$i] = 1 / ($A[$i][$j] ?: 1);
            }
        }

        return [
            'matrix' => $A,
            'idIndex' => $idIndex,
            'items' => $items
        ];
    }

    /**
     * hitung bobot dari matrix numerik
     */
    public function computeWeightsFromNumericMatrix(array $A)
    {
        $n = count($A);
        if ($n == 0) return null;

        $colsum = array_fill(0,$n,0.0);
        for ($j=0;$j<$n;$j++) for ($i=0;$i<$n;$i++) $colsum[$j] += $A[$i][$j];

        $NORM = array_fill(0,$n,array_fill(0,$n,0.0));
        for ($i=0;$i<$n;$i++) for ($j=0;$j<$n;$j++) $NORM[$i][$j] = $A[$i][$j] / ($colsum[$j] ?: 1);

        $EV = [];
        for ($i=0;$i<$n;$i++) $EV[$i] = array_sum($NORM[$i]) / $n;

        $lambda = [];
        for ($i=0;$i<$n;$i++) {
            $rowSum = 0;
            for ($j=0;$j<$n;$j++) $rowSum += $A[$i][$j] * $EV[$j];
            $lambda[$i] = $rowSum / ($EV[$i] ?: 1);
        }
        $lambda_max = array_sum($lambda) / $n;

        $CI = ($lambda_max - $n) / ($n - 1);
        $RI_TABLE = [0,0,0,0.58,0.90,1.12,1.24,1.32,1.41,1.45,1.51];
        $RI = $RI_TABLE[$n] ?? 1.51;
        $CR = $RI == 0 ? 0 : $CI / $RI;

        return [
            'eigenvector' => $EV,
            'lambda_max'  => $lambda_max,
            'CI' => $CI,
            'CR' => $CR,
            'normalisasi' => $NORM
        ];
    }

    /**
     * Save aggregated numeric matrix into ahp_matrices (expert_id = null)
     */
    public function saveAggregatedToAhpMatrices(array $A, array $idIndex)
    {
        $L = app(SFAHPService::class)->L;
        foreach ($matrix as $k1 => $cols) {
            foreach ($cols as $k2 => $ling) {
                if (!$ling) continue;

                [$mu,$nu,$pi] = $L[$ling];

                AhpMatrix::updateOrCreate(
                    [
                        'expert_id'=>$expert->id,
                        'kriteria_1_id'=>$k1,
                        'kriteria_2_id'=>$k2,
                    ],
                    compact('mu','nu','pi')
                );
            }
        }
    }
}
