<?php
namespace App\Services;

use App\Models\AlternatifLahan;
use App\Models\BatasKesesuaian;
use App\Models\KlasifikasiLahan;

class KlasifikasiService
{
    /**
     * Menentukan kelas S1, S2, S3, N berdasarkan skor total.
     */
    public function prosesKlasifikasi()
    {
        $alternatifs = AlternatifLahan::all();
        $batas = BatasKesesuaian::first();
        $hasil = [];
        foreach ($alternatifs as $alt) {
            $skor = $alt->nilai_total;
            
            if ($skor >= $batas->batas_s1)      $kelas = 'S1';
            elseif ($skor >= $batas->batas_s2)  $kelas = 'S2';
            elseif ($skor >= $batas->batas_s3)  $kelas = 'S3';
            else                                 $kelas = 'N';

            KlasifikasiLahan::updateOrCreate(
                ['alternatif_id' => $alt->id],
                [
                    'skor_normalisasi' => $skor,
                    'kelas_kesesuaian' => $kelas
                ]
            );

            $hasil[] = [
                'id' => $alt->id,
                'lokasi' => $alt->lokasi,
                'skor' => $skor,
                'kelas' => $kelas
            ];
        }

        return $hasil;
    }
}
