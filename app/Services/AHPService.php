<?php

namespace App\Services;

use App\Models\Kriteria;
use App\Models\AhpMatrix;

class AHPService
{
    public function hitungBobot()
    {
        $kriteria = Kriteria::all()->values();
        $n = $kriteria->count();

        if ($n == 0) {
            return ['error' => 'Tidak ada kriteria di database.'];
        }

        // === STEP 1: Bangun matrix perbandingan n×n ===
        $A = array_fill(0, $n, array_fill(0, $n, 1));

        foreach (AhpMatrix::all() as $m) {
            $i = $kriteria->search(fn ($item) => $item->id == $m->kriteria_1_id);
            $j = $kriteria->search(fn ($item) => $item->id == $m->kriteria_2_id);

            if ($i === false || $j === false) continue;

            $A[$i][$j] = $m->nilai_perbandingan;
            $A[$j][$i] = 1 / $m->nilai_perbandingan;
        }

        // === STEP 2: Hitung jumlah kolom ===
        $colsum = array_fill(0, $n, 0);
        for ($j = 0; $j < $n; $j++) {
            for ($i = 0; $i < $n; $i++) {
                $colsum[$j] += $A[$i][$j];
            }
        }

        // === STEP 3: Normalisasi ===
        $NORM = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $NORM[$i][$j] = $A[$i][$j] / $colsum[$j];
            }
        }

        // === STEP 4: Eigenvector / Prioritas ===
        $EV = [];
        for ($i = 0; $i < $n; $i++) {
            $EV[$i] = array_sum($NORM[$i]) / $n;
        }

        // === STEP 5: Hitung λ maks ===
        $lambda = [];
        for ($i = 0; $i < $n; $i++) {
            $rowSum = 0;
            for ($j = 0; $j < $n; $j++) {
                $rowSum += $A[$i][$j] * $EV[$j];
            }
            $lambda[$i] = $rowSum / $EV[$i];
        }

        $lambda_max = array_sum($lambda) / $n;

        // === STEP 6: CI ===
        $CI = ($lambda_max - $n) / ($n - 1);

        // === STEP 7: CR ===
        $RI_TABLE = [0, 0, 0, 0.58, 0.90, 1.12, 1.24, 1.32, 1.41, 1.45, 1.51];
        $RI = $RI_TABLE[$n] ?? 1.51;

        $CR = ($RI == 0) ? 0 : $CI / $RI;

        // === SAVE BOBOT KE DATABASE ===
        foreach ($kriteria as $i => $krit) {
            $krit->update(['bobot' => $EV[$i]]);
        }

        return [
            'matrix' => $A,
            'normalisasi' => $NORM,
            'eigenvector' => $EV,
            'lambda_max' => $lambda_max,
            'CI' => $CI,
            'CR' => $CR,
            'konsisten' => $CR <= 0.1 ? 'YA' : 'TIDAK'
        ];
    }
}
