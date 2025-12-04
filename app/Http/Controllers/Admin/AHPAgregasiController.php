<?php

namespace App\Http\Controllers\AHP;
use App\Http\Controllers\Controller;

use App\Models\AHPMatrix;
use App\Models\Kriteria;
use App\Models\Pakar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AHPAgregasiController extends Controller
{
    public function agregasi()
    {
        $kriteria = Kriteria::all()->groupBy('parent_id');
        $pakar = Pakar::all();

        if ($pakar->count() == 0) {
            return back()->with('error', 'Belum ada pakar.');
        }

        // ===========================
        // 1. Ambil semua pasangan kriteria
        // ===========================
        $mainKriteria = Kriteria::whereNull('parent_id')->get();

        $pairs = [];

        foreach ($mainKriteria as $i => $k1) {
            foreach ($mainKriteria as $j => $k2) {
                if ($i < $j) {
                    $pairs[] = [$k1->id, $k2->id];
                }
            }
        }

        // ===========================
        // 2. Hitung agregasi geometric mean berbobot
        // ===========================
        $agregate = [];

        foreach ($pairs as [$id1, $id2]) {

            $geom = 1;

            foreach ($pakar as $pk) {
                $nilai = AHPMatrix::where('expert_id', $pk->id)
                                  ->where('kriteria_1_id', $id1)
                                  ->where('kriteria_2_id', $id2)
                                  ->value('nilai_perbandingan');

                if (!$nilai) continue;

                $geom *= pow($nilai, $pk->weight);
            }

            $agregate[$id1][$id2] = $geom;
            $agregate[$id2][$id1] = 1 / $geom;
        }

        // diagonal
        foreach ($mainKriteria as $k) {
            $agregate[$k->id][$k->id] = 1;
        }

        // ===========================
        // SIMPAN MATRIX AGREGASI
        // ===========================
        DB::table('ahp_matrix_agregate')->truncate();

        foreach ($agregate as $i => $row) {
            foreach ($row as $j => $value) {
                DB::table('ahp_matrix_agregate')->insert([
                    'kriteria_1_id' => $i,
                    'kriteria_2_id' => $j,
                    'nilai' => $value
                ]);
            }
        }

        return redirect()->route('admin.ahp.final')
               ->with('success', 'Agregasi berhasil dihitung.');
    }
}
