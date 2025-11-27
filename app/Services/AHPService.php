<?php

namespace App\Services;

use App\Models\Kriteria;
use App\Models\AhpMatrix;

class AHPService
{
    public function calculateWeights()
    {
        // 1. Ambil seluruh kriteria
        $kriteria = Kriteria::all();
        $n = count($kriteria);

        // 2. Bangun matriks NxN
        $matrix = array_fill(0, $n, array_fill(0, $n, 1));

        foreach ($kriteria as $i => $k1) {
            foreach ($kriteria as $j => $k2) {
                if ($i !== $j) {
                    $nilai = AhpMatrix::where('kriteria_1_id', $k1->id)
                        ->where('kriteria_2_id', $k2->id)
                        ->value('nilai_perbandingan');

                    $matrix[$i][$j] = $nilai ?? 1;
                }
            }
        }

        // 3. Normalisasi kolom
        $colSum = [];
        for ($j = 0; $j < $n; $j++) {
            $colSum[$j] = array_sum(array_column($matrix, $j));
        }

        $norm = $matrix;
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $norm[$i][$j] = $matrix[$i][$j] / $colSum[$j];
            }
        }

        // 4. Hitung eigenvector (bobot)
        $weights = [];
        for ($i = 0; $i < $n; $i++) {
            $weights[$i] = array_sum($norm[$i]) / $n;
        }

        // 5. Simpan bobot ke DB
        foreach ($kriteria as $i => $k) {
            $k->bobot = $weights[$i];
            $k->save();
        }

        return $weights;
    }

    public function checkConsistency()
    {
        $n = Kriteria::count();
        $RI = [0, 0, 0, 0.58, 0.90, 1.12, 1.24, 1.32, 1.41];

        $weights = Kriteria::pluck('bobot')->toArray();
        $matrix = $this->buildMatrix();

        // λ_max
        $lambdaMax = 0;
        for ($i = 0; $i < $n; $i++) {
            $row = $matrix[$i];
            $lambdaMax += array_sum($row) * $weights[$i];
        }

        $CI = ($lambdaMax - $n) / ($n - 1);
        $CR = $CI / $RI[$n];

        return [
            'lambda_max' => $lambdaMax,
            'CI' => $CI,
            'CR' => $CR,
            'konsisten' => $CR < 0.1,
        ];
    }

    private function buildMatrix()
    {
        $kriteria = Kriteria::all();
        $n = count($kriteria);
        $matrix = array_fill(0, $n, array_fill(0, $n, 1));

        foreach ($kriteria as $i => $k1) {
            foreach ($kriteria as $j => $k2) {
                if ($i !== $j) {
                    $nilai = AhpMatrix::where('kriteria_1_id', $k1->id)
                        ->where('kriteria_2_id', $k2->id)
                        ->value('nilai_perbandingan');
                    $matrix[$i][$j] = $nilai ?? 1;
                }
            }
        }

        return $matrix;
    }
}
