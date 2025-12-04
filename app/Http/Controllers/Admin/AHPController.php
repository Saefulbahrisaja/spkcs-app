<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kriteria;
use App\Models\Pakar;
use App\Models\AhpMatrix;
use App\Services\AHPService;
use Illuminate\Http\Request;

class AHPController extends Controller
{
    /**
     * LIST PAKAR
     */
    public function selectExpert()
    {
        return view('admin.ahp.select-expert', [
            'pakar' => Pakar::all()
        ]);
    }

    /**
     * FORM MATRIX PER PAKAR
     */
    public function form($expert_id)
    {
        $expert = Pakar::findOrFail($expert_id);
        $kriteria = Kriteria::all();

        $values = [];

        foreach ($kriteria as $k1) {
            foreach ($kriteria as $k2) {
                $values[$k1->id][$k2->id] =
                    AhpMatrix::where([
                        'expert_id'     => $expert_id,
                        'kriteria_1_id' => $k1->id,
                        'kriteria_2_id' => $k2->id,
                    ])->value('nilai_perbandingan');
            }
        }

        return view('admin.ahp.matrix-multipakar', compact(
            'expert', 'kriteria', 'values'
        ));
    }

    /**
     * SIMPAN MATRIX PER PAKAR
     */
    public function saveMatrix(Request $r, $expert_id)
    {
        AhpMatrix::where('expert_id',$expert_id)->delete();

        foreach ($r->matrix as $k1 => $cols) {
            foreach ($cols as $k2 => $val) {
                if (!$val) continue;

                AhpMatrix::create([
                    'expert_id' => $expert_id,
                    'kriteria_1_id' => $k1,
                    'kriteria_2_id' => $k2,
                    'nilai_perbandingan' => $val
                ]);
            }
        }

        return back()->with('success','Matrix berhasil disimpan.');
    }

    /**
     * HITUNG BOBOT HASIL AGREGASI MULTI-PAKAR
     */
    public function hitung(AHPService $svc)
    {
        $hasil = $svc->hitungAgregasiMultiPakar();

        return view('admin.ahp.hasil-multipakar', compact('hasil'));
    }
}