<?php
namespace App\Services;

use App\Models\AlternatifLahan;
use App\Models\Kriteria;
use App\Models\PemeringkatanVikor;

class VIKORService
{
    /**
     * Proses VIKOR dan simpan ranking.
     */
    public function prosesVikor()
    {
        $alternatifs = AlternatifLahan::with('nilai')->get();
        $kriterias = Kriteria::all();

        $S = [];  // utility measure
        $R = [];  // regret measure
        $Q = [];  // compromise measure

        foreach ($alternatifs as $alt) {
            $sum = 0;
            $maxRegret = 0;

            foreach ($kriterias as $k) {

                // Bobot global jika ada, kalau tidak pakai bobot biasa
                $bobot = $k->bobot_global ?: $k->bobot;

                $nilai = $alt->nilai->where('kriteria_id', $k->id)->first()->nilai ?? 0;

                // **Asumsi skala baik 5 — buruk 1**
                $best = 5;
                $worst = 1;

                // Rumus VIKOR
                $temp = $bobot * (($best - $nilai) / ($best - $worst));

                $sum += $temp;
                $maxRegret = max($maxRegret, $temp);
            }

            $S[$alt->id] = $sum;
            $R[$alt->id] = $maxRegret;
        }

        // Normalisasi S dan R
        $Smin = min($S); $Smax = max($S);
        $Rmin = min($R); $Rmax = max($R);

        $v = 0.5; // compromise weight

        foreach ($alternatifs as $alt) {
            $S_norm = ($Smax - $Smin) == 0 ? 0 : ($S[$alt->id] - $Smin) / ($Smax - $Smin);
            $R_norm = ($Rmax - $Rmin) == 0 ? 0 : ($R[$alt->id] - $Rmin) / ($Rmax - $Rmin);

            $Q[$alt->id] = $v * $S_norm + (1 - $v) * $R_norm;
        }

        // Urutkan berdasarkan Q naik
        asort($Q);

        $hasil = [];
        $rank = 1;

        foreach ($Q as $id => $q) {

            PemeringkatanVikor::updateOrCreate(
                ['alternatif_id' => $id],
                [
                    'v_value'       => $R[$id],
                    'q_value'       => $q,
                    'hasil_ranking' => $rank
                ]
            );

            $hasil[] = [
                'alternatif_id' => $id,
                'ranking' => $rank,
                'q_value' => $q
            ];

            $rank++;
        }

        return $hasil;
    }
}
