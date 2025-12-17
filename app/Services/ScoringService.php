<?php
namespace App\Services;

use App\Models\AlternatifLahan;
use App\Models\Kriteria;

class ScoringService
{
    /**
     * Hitung skor total alternatif:
     * - Jika punya sub-kriteria → pakai bobot_global
     * - Jika tidak punya sub → pakai bobot
     */
    public function hitungSkorAlternatif()
    {
        $alternatifs = AlternatifLahan::with('nilai')->get();
        $kriteria = Kriteria::all();

        foreach ($alternatifs as $alt) {
            $total = 0;

            foreach ($kriteria as $k) {

                // Pilih bobot terbaik
                $bobot = $k->bobot_global ?: $k->bobot;

                // Ambil nilai alternatif
                $nilaiAlt = $alt->nilai
                    ->where('kriteria_id', $k->id)
                    ->first()
                    ??
                    $alt->nilai->where('atribut_nama', $k->nama_kriteria)->first();
                if ($nilaiAlt) {
                    $total += $nilaiAlt->nilai * $bobot;
                
                }
            }

            $alt->nilai_total = $total;
            $alt->save();
        }

        return true;
    }
}
