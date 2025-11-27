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

    public function storeMatrix(Request $request)
    {
        foreach ($request->matrix as $k1 => $row) {
            foreach ($row as $k2 => $val) {
                AhpMatrix::updateOrCreate(
                    [
                        'kriteria_1_id' => $k1,
                        'kriteria_2_id' => $k2
                    ],
                    [
                        'nilai_perbandingan' => $val
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
