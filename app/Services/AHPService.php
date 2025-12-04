<?php

namespace App\Services;

use App\Models\Kriteria;
use App\Models\AhpMatrix;
use App\Models\Expert;
use App\Models\ExpertAhpMatrix;


class AHPService
{
    /**
     * HITUNG AHP LENGKAP (KRITERIA + SUB KRITERIA)
     */
    public function hitungBobot()
    {
        return [
            'kriteria'     => $this->hitungBobotKriteria(),
            'subkriteria'  => $this->hitungBobotSubKriteria()
        ];
    }

    /**
     * ================
     * LEVEL 1 – KRITERIA UTAMA
     * ================
     */
    public function hitungBobotKriteria()
    {
        $kriteria = Kriteria::whereNull('parent_id')->get()->values();

        return $this->hitungMatriksAHP($kriteria, $isSub = false);
    }

    /**
     * ================
     * LEVEL 2 – SUB-KRITERIA
     * ================
     */
    public function hitungBobotSubKriteria()
    {
        $result = [];
        $parents = Kriteria::whereNull('parent_id')->get();

        foreach ($parents as $parent) {

            $subs = $parent->sub;

            if ($subs->count() <= 1) continue;

            $result[$parent->id] = $this->hitungMatriksAHP($subs, $isSub = true);
        }

        return $result;
    }


    /**
     * ==============================================
     *   MULTI-EXPERT MATRIX AGGREGATION (SWGM)
     * ==============================================
     */
    private function ambilMatrixPakar($items)
    {
        $itemIds = $items->pluck('id')->toArray();
        $experts = Expert::all();

        if ($experts->count() == 0) {
            return null; // fallback ke single AHP
        }

        $allMatrices = [];
        $weights = [];

        foreach ($experts as $expert) {
            $rows = AhpMatrix::where('expert_id', $expert->id)
                ->whereIn('kriteria_1_id', $itemIds)
                ->whereIn('kriteria_2_id', $itemIds)
                ->get();

            $matrix = [];
            foreach ($items as $i => $a) {
                foreach ($items as $j => $b) {

                    if ($a->id == $b->id) {
                        $matrix[$i][$j] = 1;
                        continue;
                    }

                    $val = $rows->where('kriteria_1_id',$a->id)
                                ->where('kriteria_2_id',$b->id)
                                ->value('nilai_perbandingan');

                    if ($val) {
                        $matrix[$i][$j] = $val;
                    } else {
                        $reverse = $rows->where('kriteria_1_id',$b->id)
                                        ->where('kriteria_2_id',$a->id)
                                        ->value('nilai_perbandingan');

                        $matrix[$i][$j] = $reverse ? 1 / $reverse : 1;
                    }
                }
            }

            $allMatrices[] = $matrix;
            $weights[] = $expert->weight ?: 1; // default equal
        }

        // Normalisasi bobot
        $sum = array_sum($weights);
        $weights = array_map(fn($w)=>$w/$sum, $weights);

        return $this->aggregateExpertMatrix($allMatrices, $weights);
    }

    /**
     * ==============================================
     *           AGGREGASI MULTI-PAKAR (SWGM)
     * ==============================================
     */
    public function aggregateExpertMatrix($matrices, $weights)
    {
        $groupMatrix = [];

        foreach ($matrices[0] as $i => $row) {
            foreach ($row as $j => $val) {

                $product = 1;

                foreach ($matrices as $k => $expertMatrix) {
                    $product *= pow($expertMatrix[$i][$j], $weights[$k]);
                }

                $groupMatrix[$i][$j] = $product;
            }
        }

        return $groupMatrix;
    }


    /**
     * ==============================================
     *        INTI PERHITUNGAN AHP (agregated)
     * ==============================================
     */
    private function hitungMatriksAHP($items, $isSub = false)
    {
        $n = $items->count();
        if ($n == 0) return ['error' => 'Data kosong'];

        /**
         * 1) AMBIL MATRIX GABUNGAN PAKAR
         */
        $A = $this->ambilMatrixPakar($items);

        /**
         * Jika TIDAK ada pakar → fallback ke tabel ahp_matrices (lama)
         */
        if (!$A) {
            $A = array_fill(0, $n, array_fill(0, $n, 1));
            foreach (AhpMatrix::all() as $m) {
                $i = $items->search(fn($x) => $x->id == $m->kriteria_1_id);
                $j = $items->search(fn($x) => $x->id == $m->kriteria_2_id);

                if ($i !== false && $j !== false) {
                    $A[$i][$j] = $m->nilai_perbandingan;
                    $A[$j][$i] = 1 / $m->nilai_perbandingan;
                }
            }
        }

        /**
         * 2) NORMALISASI
         */
        $colsum = array_fill(0, $n, 0);
        for ($j=0; $j<$n; $j++) {
            for ($i=0; $i<$n; $i++) {
                $colsum[$j] += $A[$i][$j];
            }
        }

        $N = [];
        for ($i=0; $i<$n; $i++) {
            for ($j=0; $j<$n; $j++) {
                $N[$i][$j] = $A[$i][$j] / $colsum[$j];
            }
        }

        /**
         * 3) EIGENVECTOR
         */
        $EV = [];
        for ($i=0; $i<$n; $i++) {
            $EV[$i] = array_sum($N[$i]) / $n;
        }

        /**
         * 4) LAMBDA MAX
         */
        $lambda = [];
        for ($i=0; $i<$n; $i++) {
            $s = 0;
            for ($j=0; $j<$n; $j++) {
                $s += $A[$i][$j] * $EV[$j];
            }
            $lambda[$i] = $s / $EV[$i];
        }

        $lambda_max = array_sum($lambda) / $n;

        /**
         * 5) CI & CR
         */
        $CI = ($lambda_max - $n) / ($n - 1);
        $RI_TABLE = [0,0,0,0.58,0.90,1.12,1.24,1.32,1.41,1.45];
        $RI = $RI_TABLE[$n] ?? 1.45;
        $CR = $RI == 0 ? 0 : $CI / $RI;

        /**
         * 6) SIMPAN BOBOT
         */
        foreach ($items as $i => $obj) {
            $obj->update(['bobot' => $EV[$i]]);
        }

        return [
            'items'        => $items,
            'matrix'       => $A,
            'normalisasi'  => $N,
            'eigenvector'  => $EV,
            'lambda_max'   => $lambda_max,
            'CI'           => $CI,
            'CR'           => $CR,
            'konsisten'    => $CR <= 0.1 ? 'YA' : 'TIDAK'
        ];
    }
}
