<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Kriteria;
use App\Models\AlternatifLahan;
use App\Models\NilaiAlternatif;
use Illuminate\Support\Facades\Hash;

class SPKSeeder extends Seeder
{
    public function run()
    {
        $this->seedUsers();
        $this->seedKriteria();
        $this->seedAlternatif();
        $this->seedNilaiAlternatif();
    }


    private function seedUsers()
    {
        User::create([
            'nama' => 'Admin Sistem',
            'email' => 'admin@spk.com',
            'username' => 'admin',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);

        User::create([
            'nama' => 'Dinas Pertanian Banten',
            'email' => 'dinas@spk.com',
            'username' => 'dinas',
            'password' => Hash::make('password'),
            'role' => 'dinas'
        ]);

        User::create([
            'nama' => 'Penyuluh Kecamatan',
            'email' => 'penyuluh@spk.com',
            'username' => 'penyuluh',
            'password' => Hash::make('password'),
            'role' => 'penyuluh'
        ]);
    }


    private function seedKriteria()
    {
        $kriteria = [
            ['nama' => 'Curah Hujan', 'tipe' => 'benefit'],
            ['nama' => 'Suhu Rata-Rata', 'tipe' => 'benefit'],
            ['nama' => 'Kedalaman Tanah', 'tipe' => 'benefit'],
            ['nama' => 'Tekstur Tanah', 'tipe' => 'benefit'],
            ['nama' => 'Drainase', 'tipe' => 'cost'],
            ['nama' => 'C-Organik', 'tipe' => 'benefit'],
            ['nama' => 'pH Tanah', 'tipe' => 'benefit'],
            ['nama' => 'Nitrogen', 'tipe' => 'benefit'],
            ['nama' => 'Fosfor', 'tipe' => 'benefit'],
            ['nama' => 'Kalium', 'tipe' => 'benefit'],
            ['nama' => 'Bahaya Banjir', 'tipe' => 'cost'],
            ['nama' => 'Lereng', 'tipe' => 'cost'],
        ];

        foreach ($kriteria as $k) {
            Kriteria::create([
                'nama_kriteria' => $k['nama'],
                'tipe' => $k['tipe'],
                'bobot' => null // akan dihitung oleh AHP
            ]);
        }
    }


    private function seedAlternatif()
    {
        $alternatif = [
            ['lokasi' => 'Pandeglang – Kecamatan Cikeusik', 'geojson_path' => 'geojson/p1.geojson'],
            ['lokasi' => 'Serang – Kecamatan Pamarayan', 'geojson_path' => 'geojson/p2.geojson'],
            ['lokasi' => 'Lebak – Kecamatan Malingping', 'geojson_path' => 'geojson/p3.geojson'],
            ['lokasi' => 'Pandeglang – Kecamatan Picung', 'geojson_path' => 'geojson/p4.geojson'],
            ['lokasi' => 'Lebak – Kecamatan Warunggunung', 'geojson_path' => 'geojson/p5.geojson'],
        ];

        foreach ($alternatif as $a) {
            AlternatifLahan::create([
                'lokasi' => $a['lokasi'],
                'geojson_path' => $a['geojson_path'],
                'nilai_skor' => null,
                'nilai_total' => null,
                'kelas_kesesuaian' => null
            ]);
        }
    }


    private function seedNilaiAlternatif()
    {
        $kriteria = Kriteria::all();
        $alternatif = AlternatifLahan::all();

        foreach ($alternatif as $alt) {
            foreach ($kriteria as $k) {
                NilaiAlternatif::create([
                    'alternatif_id' => $alt->id,
                    'kriteria_id' => $k->id,
                    'nilai' => rand(1, 100) // dummy realistis
                ]);
            }
        }
    }
}
