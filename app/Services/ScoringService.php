<?php
namespace App\Services;

use App\Models\Kriteria;
use App\Models\AlternatifLahan;
use App\Models\NilaiAlternatif;
use Illuminate\Support\Facades\DB;

class ScoringService
{
    /**
     * Hitung skor tiap alternatif:
     * - Normalisasi per kriteria (min-max)
     * - Jika benefit: (value - min)/(max - min)
     * - Jika cost: (max - value)/(max - min)
     * - Bobot dari table kriteria (AHP)
     * - Simpan skor per nilai_alternatif.skor dan alternatif_lahan.nilai_skor & nilai_total
     */
    public function hitungSkorAlternatif()
    {
        $kriteria = Kriteria::all();
        $alternatif = AlternatifLahan::with('nilai')->get();

        // 1) Hitung min & max per kriteria
        $minMax = [];
        foreach ($kriteria as $k) {
            $values = NilaiAlternatif::where('kriteria_id', $k->id)
                ->pluck('nilai')
                ->filter(fn($v) => $v !== null)
                ->toArray();

            if (empty($values)) {
                $minMax[$k->id] = ['min' => null, 'max' => null];
                continue;
            }

            $minMax[$k->id] = [
                'min' => min($values),
                'max' => max($values)
            ];
        }

        DB::transaction(function() use ($alternatif, $kriteria, $minMax) {
            foreach ($alternatif as $alt) {
                $weightedSum = 0.0;
                // update setiap nilai_alternatif.skor
                foreach ($alt->nilai as $n) {
                    $k = $kriteria->firstWhere('id', $n->kriteria_id);
                    if (!$k) continue;

                    $mm = $minMax[$k->id];
                    $min = $mm['min']; $max = $mm['max'];

                    // hindari pembagian 0
                    if ($min === null || $max === null || $max == $min) {
                        $norm = 0; // fallback
                    } else {
                        if ($k->tipe === 'benefit') {
                            $norm = ($n->nilai - $min) / ($max - $min);
                        } else { // cost
                            $norm = ($max - $n->nilai) / ($max - $min);
                        }
                    }

                    $skor_terbobot = $norm * ($k->bobot ?? 0);
                    $weightedSum += $skor_terbobot;

                    // simpan skor normalisasi ke nilai_alternatif
                    $n->skor = $norm;
                    $n->save();
                }

                // nilai_skor -> sum bobot ter-normalisasi
                $alt->nilai_skor = $weightedSum;
                // nilai_total -> bisa digunakan sebagai agregat (mis: 0..1)
                $alt->nilai_total = $weightedSum;
                $alt->save();
            }
        });

        return true;
    }
}
