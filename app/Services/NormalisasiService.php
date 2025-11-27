<?php

namespace App\Services;

use App\Models\AlternatifLahan;
use App\Models\NilaiAlternatif;
use App\Models\Kriteria;
use App\Models\KlasifikasiLahan;

class NormalisasiService
{
    public function klasifikasi()
    {
        $kriteria = Kriteria::all();
        $alternatif = AlternatifLahan::all();

        // Ambil nilai mentah per kriteria
        $nilaiKriteria = [];
        foreach ($kriteria as $k) {
            $nilaiKriteria[$k->id] = NilaiAlternatif::where('kriteria_id', $k->id)->pluck('nilai')->toArray();
        }

        // Normalize
        foreach ($alternatif as $alt) {
            $totalSkor = 0;

            foreach ($kriteria as $k) {

                $nilai = NilaiAlternatif::where('alternatif_id', $alt->id)
                        ->where('kriteria_id', $k->id)
                        ->value('nilai');

                $min = min($nilaiKriteria[$k->id]);
                $max = max($nilaiKriteria[$k->id]);

                if ($max == $min) {
                    $norm = 1; // fallback
                } else {

                    // Benefit criteria (lebih besar lebih baik)
                    if ($k->tipe == 'benefit') {
                        $norm = ($nilai - $min) / ($max - $min);
                    }

                    // Cost criteria (lebih kecil lebih baik)
                    else {
                        $norm = ($max - $nilai) / ($max - $min);
                    }
                }

                // Simpan skor normalisasi
                NilaiAlternatif::where('alternatif_id', $alt->id)
                    ->where('kriteria_id', $k->id)
                    ->update(['skor' => $norm]);

                $totalSkor += $norm * $k->bobot;
            }

            // Tentukan kelas S1,S2,S3,N
            $kelas = $this->tentukanKelas($totalSkor);

            // Simpan ke tabel klasifikasi
            KlasifikasiLahan::updateOrCreate(
                ['alternatif_id' => $alt->id],
                [
                    'skor_normalisasi' => $totalSkor,
                    'kelas_kesesuaian' => $kelas
                ]
            );

            // Update tabel alternatif
            $alt->update([
                'nilai_total' => $totalSkor,
                'kelas_kesesuaian' => $kelas
            ]);
        }

        return AlternatifLahan::with('klasifikasi')->get();
    }


    private function tentukanKelas($skor)
    {
        if ($skor >= 0.75) return 'S1'; // Sangat sesuai
        if ($skor >= 0.50) return 'S2'; // Cukup sesuai
        if ($skor >= 0.25) return 'S3'; // Marginal
        return 'N'; // Tidak sesuai
    }
}
