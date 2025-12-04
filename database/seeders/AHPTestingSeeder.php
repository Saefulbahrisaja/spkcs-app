<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AHPTestingSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Hapus tabel yang punya FK ke kriteria / alternatif dulu
        DB::table('ahp_matrices')->truncate();
        DB::table('nilai_alternatifs')->truncate();
        DB::table('alternatif_lahans')->truncate();
        DB::table('kriterias')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        // ============================
        // 1. INSERT KRITERIA
        // ============================
        $kriteria = [
            ['nama_kriteria' => 'Iklim'],
            ['nama_kriteria' => 'Media Perakaran'],
            ['nama_kriteria' => 'Retensi Hara'],
            ['nama_kriteria' => 'Nutrisi Tersedia'],
            ['nama_kriteria' => 'Bahaya Banjir'],
            ['nama_kriteria' => 'Penyiapan Lahan'],
        ];

        DB::table('kriterias')->insert($kriteria);

        $kriteriaIds = DB::table('kriterias')->pluck('id')->toArray();

        // ============================
        // 2. PAIRWISE MATRIX AHP
        // ============================
        // Tabel pairwise sesuai skenario
        $pairwise = [
            // Iklim
            [1, 1, 3, 4, 5, 6, 7],
            // Media Perakaran
            [2, 1/3, 1, 2, 3, 4, 5],
            // Retensi Hara
            [3, 1/4, 1/2, 1, 2, 3, 4],
            // Nutrisi Tersedia
            [4, 1/5, 1/3, 1/2, 1, 2, 3],
            // Bahaya Banjir
            [5, 1/6, 1/4, 1/3, 1/2, 1, 2],
            // Penyiapan Lahan
            [6, 1/7, 1/5, 1/4, 1/3, 1/2, 1],
        ];

        foreach ($kriteriaIds as $i => $k1) {
            foreach ($kriteriaIds as $j => $k2) {
                DB::table('ahp_matrices')->insert([
                    'kriteria_1_id' => $k1,
                    'kriteria_2_id' => $k2,
                    'nilai_perbandingan' => $pairwise[$i][$j],
                ]);
            }
        }

        // ============================
        // 3. ALTERNATIF
        // ============================
        $alternatif = [
            ['lokasi' => 'Serang',     'lat' => -6.11, 'lng' => 106.16],
            ['lokasi' => 'Pandeglang', 'lat' => -6.31, 'lng' => 106.10],
            ['lokasi' => 'Lebak',      'lat' => -6.56, 'lng' => 106.14],
        ];

        DB::table('alternatif_lahans')->insert($alternatif);

        $alternatifIds = DB::table('alternatif_lahans')->pluck('id')->toArray();

        // ============================
        // 4. NILAI ALTERNATIF
        // ============================

        // S1 – SERANG
        $nilaiSerang = [5,4,4,5,2,4];

        // S2 – PANDEGLANG
        $nilaiPandeglang = [3,3,3,3,3,3];

        // S3 – LEBAK
        $nilaiLebak = [2,2,2,1,4,2];

        $nilaiAlternatif = [
            $nilaiSerang,
            $nilaiPandeglang,
            $nilaiLebak
        ];

        foreach ($alternatifIds as $index => $altId) {
            foreach ($kriteriaIds as $kIndex => $kId) {
                DB::table('nilai_alternatifs')->insert([
                    'alternatif_id' => $altId,
                    'kriteria_id'   => $kId,
                    'nilai'         => $nilaiAlternatif[$index][$kIndex],
                    'skor'          => null, // skor dihitung otomatis oleh evaluasi
                ]);
            }
        }

        echo "Seeder AHP testing berhasil dibuat.\n";
    }
}
