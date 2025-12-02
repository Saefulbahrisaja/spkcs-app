<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AlternatifLahan;
use App\Models\Kriteria;
use App\Models\KlasifikasiLahan;
use App\Models\PemeringkatanVikor;

class EvaluasiLahanCommand extends Command
{
    protected $signature = 'evaluasi:lahan';
    protected $description = 'Hitung AHP, Klasifikasi, dan VIKOR secara otomatis';

    public function handle()
    {
        $this->info("=== MEMULAI PERHITUNGAN EVALUASI LAHAN ===");

        $this->hitungAHP();
        $this->info("✔ AHP selesai.");

        $this->hitungSkorTotal();
        $this->info("✔ Skor total alternatif selesai.");

        $this->hitungKlasifikasi();
        $this->info("✔ Klasifikasi lahan selesai.");

        $this->hitungVIKOR();
        $this->info("✔ VIKOR selesai.");

        $this->info("=== SELESAI. SEMUA HASIL TERSIMPAN ===");

        return Command::SUCCESS;
    }

    // ----------------------------------------------------
    // 1) AHP → simpan bobot kriteria
    // ----------------------------------------------------
    private function hitungAHP()
    {
        $kriteria = Kriteria::all();
        $n = count($kriteria);

        if ($n == 0) return;

        // ambil matriks dari database (berdasarkan tabel pairwise)
        $matrix = [];
        foreach ($kriteria as $i => $ki) {
            foreach ($kriteria as $j => $kj) {
                if ($i == $j) {
                    $matrix[$i][$j] = 1;
                } else {
                    $pair = \DB::table('ahp_matrices')->where([
                        'kriteria_1_id' => $ki->id,
                        'kriteria_2_id' => $kj->id
                    ])->first();

                    $matrix[$i][$j] = $pair ? $pair->nilai_perbandingan : 1;
                }
            }
        }

        // Hitung kolom sum
        $colSum = [];
        for ($j = 0; $j < $n; $j++) {
            $colSum[$j] = array_sum(array_column($matrix, $j));
        }

        // Normalisasi
        $norm = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $norm[$i][$j] = $matrix[$i][$j] / $colSum[$j];
            }
        }

        // Eigenvector / bobot
        $bobot = [];
        for ($i = 0; $i < $n; $i++) {
            $bobot[$i] = array_sum($norm[$i]) / $n;
        }

        // SIMPAN
        foreach ($kriteria as $i => $k) {
            $k->bobot = $bobot[$i];
            $k->save();
        }
    }

    // ----------------------------------------------------
    // 2) Hitung Skor Total Alternatif
    // ----------------------------------------------------
    private function hitungSkorTotal()
    {
        $alternatifs = AlternatifLahan::with('nilai')->get();
        $kriteria = Kriteria::all();

        foreach ($alternatifs as $alt) {

            $total = 0;
            foreach ($kriteria as $k) {
                $nilaiAlt = $alt->nilai->where('kriteria_id', $k->id)->first();
                if ($nilaiAlt) {
                    $total += $nilaiAlt->nilai * $k->bobot;
                }
            }

            $alt->nilai_total = $total;
            $alt->save();
        }
    }

    // ----------------------------------------------------
    // 3) Klasifikasi lahan berdasarkan skor total
    // ----------------------------------------------------
    private function hitungKlasifikasi()
    {
        $alternatifs = AlternatifLahan::all();
        $batas = \App\Models\BatasKesesuaian::first();

        $S1 = $batas->batas_s1;
        $S2 = $batas->batas_s2;
        $S3 = $batas->batas_s3;

        foreach ($alternatifs as $a) {
            $skor = $a->nilai_total;

            if ($skor >= $S1) $kelas = 'S1';
            elseif ($skor >= $S2) $kelas = 'S2';
            elseif ($skor >= $S3) $kelas = 'S3';
            else $kelas = 'N';

            KlasifikasiLahan::updateOrCreate(
                ['alternatif_id' => $a->id],
                [
                    'skor_normalisasi' => $skor,
                    'kelas_kesesuaian' => $kelas
                ]
            );
        }
    }

    // ----------------------------------------------------
    // 4) VIKOR Ranking
    // ----------------------------------------------------
   private function hitungVIKOR()
{
    $kriteria = Kriteria::all();
    $alternatifs = AlternatifLahan::with('nilai')->get();

    $S = [];
    $R = [];
    $Q = [];

    foreach ($alternatifs as $a) {
        $sum = 0;
        $max = 0;

        foreach ($kriteria as $k) {
            $nilai = $a->nilai->where('kriteria_id', $k->id)->first()->nilai ?? 0;
            $best = 5;
            $worst = 1;

            $temp = $k->bobot * (($best - $nilai) / ($best - $worst));
            $sum += $temp;
            if ($temp > $max) $max = $temp;
        }

        $S[$a->id] = $sum;
        $R[$a->id] = $max;
    }

    $Smin = min($S); 
    $Smax = max($S);
    $Rmin = min($R); 
    $Rmax = max($R);

    $v = 0.5;

    foreach ($alternatifs as $a) {

        // --- FIX Division by zero ---
        $S_div = ($Smax - $Smin) == 0 ? 0 : ($S[$a->id] - $Smin) / ($Smax - $Smin);
        $R_div = ($Rmax - $Rmin) == 0 ? 0 : ($R[$a->id] - $Rmin) / ($Rmax - $Rmin);

        $Q[$a->id] = $v * $S_div + (1 - $v) * $R_div;
    }

    asort($Q);
    $ranking = 1;

    foreach ($Q as $altId => $q) {
        PemeringkatanVikor::updateOrCreate(
            ['alternatif_id' => $altId],
            [
                'v_value'       => $R[$altId],
                'q_value'       => $q,
                'hasil_ranking' => $ranking++
            ]
        );
    }
}

}
