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

        foreach ($alternatif as $alt) {
            $sumWeighted = 0;
            $maxRegret = 0;

            foreach ($kriteria as $k) {
                $nilai = NilaiAlternatif::where('alternatif_id', $alt->id)
                    ->where('kriteria_id', $k->id)
                    ->value('skor');

                $weighted = $nilai * $k->bobot;

                // S_j = Σ w_i * f_ij
                $sumWeighted += $weighted;

                // R_j = max (w_i * f_ij)
                if ($weighted > $maxRegret) {
                    $maxRegret = $weighted;
                }
            }

            $S[$alt->id] = $sumWeighted;
            $R[$alt->id] = $maxRegret;
        }

        // Normalisasi
        $S_min = min($S);
        $S_max = max($S);
        $R_min = min($R);
        $R_max = max($R);

        $Q = [];
        $v = 0.5; // Default VIKOR parameter

        foreach ($alternatif as $alt) {
            $Q[$alt->id] = 
                $v * (($S[$alt->id] - $S_min) / ($S_max - $S_min)) +
                (1 - $v) * (($R[$alt->id] - $R_min) / ($R_max - $R_min));

            // Simpan
            PemeringkatanVikor::updateOrCreate(
                ['alternatif_id' => $alt->id],
                [
                    'v_value' => $S[$alt->id],
                    'q_value' => $Q[$alt->id],
                    'hasil_ranking' => null
                ]
            );
        }

        // Ranking berdasarkan Q
        $sorted = collect($Q)->sort()->toArray();
        $rank = 1;

        foreach ($sorted as $altId => $score) {
            PemeringkatanVikor::where('alternatif_id', $altId)
                ->update(['hasil_ranking' => $rank++]);
        }

        return $sorted;
    }
}
