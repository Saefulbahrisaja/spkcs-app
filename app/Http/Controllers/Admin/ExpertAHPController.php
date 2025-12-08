<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expert;
use App\Models\Kriteria;
use App\Models\AhpMatrix;
use App\Services\AHPMultiExpertService;

class ExpertAHPController extends Controller
{
    public function index()
    {
        $experts  = Expert::all();
        $kriteria = Kriteria::with('sub')->whereNull('parent_id')->get();

        return view('admin.ahp.expert-index', compact('experts','kriteria'));
    }

    public function createExpert(Request $r)
    {
        $r->validate(['name' => 'required']);

        Expert::create([
            'name'   => $r->name,
            'email'  => $r->email ?? null,
            'weight' => $r->weight ?? 0
        ]);

        return back()->with('success','Pakar ditambahkan');
    }

    
    public function inputMatrixForm($expertId)
    {
        $expert = Expert::findOrFail($expertId);
        $all = Kriteria::with('sub')->get();
        $parents = $all->whereNull('parent_id')->values();
        $existing = AhpMatrix::where('expert_id', $expert->id)->get();
        $values = [];
        foreach ($existing as $row) {
            $values[$row->kriteria_1_id][$row->kriteria_2_id] = (float)$row->nilai_perbandingan;
        }
        $subValues = [];
        foreach ($parents as $parent) {
            foreach ($parent->sub as $s1) {
                foreach ($parent->sub as $s2) {
                    $subValues[$parent->id][$s1->id][$s2->id] =
                        $values[$s1->id][$s2->id] ?? null;
                }
            }
        }

        return view('admin.ahp.expert-matrix', [
            'expert'    => $expert,
            'kriteria'  => $all,
            'parents'   => $parents,
            'existing'  => $existing,
            'values'    => $values,
            'subValues' => $subValues
        ]);
    }

    public function saveExpertMatrix(Request $r, $expertId)
    {
        $expert = Expert::findOrFail($expertId);
        $matrix = $r->input('matrix', []);

        foreach ($matrix as $k1 => $cols) {
            foreach ($cols as $k2 => $val) {
                if ($val === null || $val === '') continue;

                AhpMatrix::updateOrCreate(
                    [
                        'expert_id'      => $expert->id,
                        'kriteria_1_id'  => $k1,
                        'kriteria_2_id'  => $k2
                    ],
                    [
                        'nilai_perbandingan' => (float)$val
                    ]
                );
            }
        }

        return back()->with('success','Matrix pakar tersimpan');
    }

    /**
     * AGREGASI + HITUNG BOBOT
     */
    public function aggregateAndCompute(AHPMultiExpertService $svc)
    {
        $items = Kriteria::whereNull('parent_id')->get();

        $res = $svc->aggregateMatricesForItems($items);

        if (!$res)
            return back()->with('error','Tidak ada pakar atau data.');

        $weightsRes = $svc->computeWeightsFromNumericMatrix($res['matrix']);

        $svc->saveAggregatedToAhpMatrices($res['matrix'], $res['idIndex']);

        // Simpan bobot ke tabel kriteria
        foreach ($res['idIndex'] as $idx => $id) {
            $w = $weightsRes['eigenvector'][$idx] ?? 0;
            Kriteria::where('id', $id)->update(['bobot' => $w]);
        }

        return back()->with('success','Agregasi & bobot berhasil dihitung');
    }
}
