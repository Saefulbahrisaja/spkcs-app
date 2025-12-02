<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AHPService;
use App\Models\Kriteria;
use App\Models\AhpMatrix;
use Illuminate\Http\Request;

class AHPController extends Controller
{
    public function formMatrix()
    {
        $kriteria = Kriteria::all();
        $values = [];

        foreach ($kriteria as $k1) {
            foreach ($kriteria as $k2) {
                $values[$k1->id][$k2->id] = AhpMatrix::where('kriteria_1_id',$k1->id)
                    ->where('kriteria_2_id',$k2->id)->value('nilai_perbandingan');
            }
        }

        return view('admin.ahp.matrix', compact('kriteria','values'));
    }

    public function matrixForm()
{
    $kriteria = Kriteria::all();

    // Ambil matrix jika ada
    $matrix = [];
    foreach (AhpMatrix::all() as $m) {
        $matrix[$m->kriteria_1_id][$m->kriteria_2_id] = $m->nilai_perbandingan;
    }

    return view('admin.ahp.matrix', compact('kriteria', 'matrix'));
}

    public function saveMatrix(Request $r)
    {
        AhpMatrix::truncate();

        foreach ($r->matrix as $k1 => $cols) {
            foreach ($cols as $k2 => $val) {
                if ($val != null) {
                    AhpMatrix::create([
                        'kriteria_1_id' => $k1,
                        'kriteria_2_id' => $k2,
                        'nilai_perbandingan' => $val
                    ]);
                }
            }
        }

        return back()->with('success', 'Matrix berhasil disimpan.');
    }


    public function storeMatrix(Request $request)
    {
        if (!isset($request->matrix) || !is_array($request->matrix)) {
            return back()->with('error', 'Matriks tidak ditemukan');
        }

        foreach ($request->matrix as $k1 => $row) {
            if (!is_array($row)) {
                continue;
            }
            foreach ($row as $k2 => $val) {
                // If value is null or empty, set diagonal to 1, otherwise skip saving
                if ($val === null || $val === '') {
                    if ($k1 == $k2) {
                        $val = 1;
                    } else {
                        continue;
                    }
                }

                // Ensure we store a numeric value
                if (!is_numeric($val)) {
                    if ($k1 == $k2) {
                        $val = 1;
                    } else {
                        continue;
                    }
                }

                $nilai = (float) $val;

                AhpMatrix::updateOrCreate(
                    [
                        'kriteria_1_id' => $k1,
                        'kriteria_2_id' => $k2
                    ],
                    [
                        'nilai_perbandingan' => $nilai
                    ]
                );
            }
        }

        return back()->with('success',"Matriks AHP disimpan");
    }

    public function hitungBobot(AHPService $ahp)
    {
        $bobot = $ahp->calculateWeights();
        $konsistensi = $ahp->checkConsistency();

        return view('admin.ahp.hasil', compact('bobot','konsistensi'));
    }

    
}
