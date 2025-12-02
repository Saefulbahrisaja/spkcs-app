<?php

namespace App\Services;

use App\Models\AlternatifLahan;
use App\Models\NilaiAlternatif;
use App\Models\Kriteria;
use App\Models\PemeringkatanVikor;

class VIKORService
{
    public function calculateVikor()
    {
        $kriteria = Kriteria::all();
        $alternatif = AlternatifLahan::all();

        $S = [];
        $R = [];

        // Step 1: Hitung S dan R
        foreach ($alternatif as $alt) {
            $sumWeighted = 0;
            $maxRegret = 0;

            foreach ($kriteria as $k) {

                $nilai = NilaiAlternatif::where('alternatif_id', $alt->id)
                    ->where('kriteria_id', $k->id)
                    ->value('skor') ?? 0;

                $weighted = $nilai * ($k->bobot ?? 0);

                $sumWeighted += $weighted;

                if ($weighted > $maxRegret) {
                    $maxRegret = $weighted;
                }
            }

            $S[$alt->id] = $sumWeighted;
            $R[$alt->id] = $maxRegret;
        }

        // Step 2: Normalisasi
        $S_min = min($S);
        $S_max = max($S);
        $R_min = min($R);
        $R_max = max($R);

        $Q = [];
        $v = 0.5; // Default parameter VIKOR

        foreach ($alternatif as $alt) {

            // HANDLE division by zero
            $S_ratio = ($S_max - $S_min) == 0
                ? 0
                : (($S[$alt->id] - $S_min) / ($S_max - $S_min));

            $R_ratio = ($R_max - $R_min) == 0
                ? 0
                : (($R[$alt->id] - $R_min) / ($R_max - $R_min));

            $Q[$alt->id] = $v * $S_ratio + (1 - $v) * $R_ratio;

            PemeringkatanVikor::updateOrCreate(
                ['alternatif_id' => $alt->id],
                [
                    'v_value' => $S[$alt->id],
                    'q_value' => $Q[$alt->id],
                    'hasil_ranking' => null
                ]
            );
        }

        // Step 3: Ranking berdasarkan Q
        $sorted = collect($Q)->sort()->toArray();
        $rank = 1;

        foreach ($sorted as $altId => $score) {
            PemeringkatanVikor::where('alternatif_id', $altId)
                ->update(['hasil_ranking' => $rank++]);
        }

        return $sorted;
    }
}
