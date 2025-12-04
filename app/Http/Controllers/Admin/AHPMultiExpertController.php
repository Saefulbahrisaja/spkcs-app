<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expert;
use App\Models\Kriteria;
use App\Models\AhpMatrix;
use App\Services\AHPMultiExpertService;

class AHPMultiExpertController extends Controller
{
    public function index()
    {
        $experts = Expert::all();
        return view('admin.ahp.experts.index', compact('experts'));
    }

    public function createExpert()
    {
        return view('admin.ahp.experts.create');
    }

    public function storeExpert(Request $r)
    {
        $r->validate(['name'=>'required']);
        Expert::create($r->only('name','email','weight'));
        return back()->with('success','Expert tersimpan.');
    }

    public function matrixForm(Expert $expert)
    {
        // Semua kriteria
        $all = Kriteria::with('sub')->get();
        $parents = $all->where('parent_id', null);
        $existing = AhpMatrix::where('expert_id', $expert->id)
            ->get()
            ->groupBy('kriteria_1_id');

        // ====== BUILD $values untuk kriteria utama ======
        $values = [];
        foreach ($existing as $k1 => $rows) {
            foreach ($rows as $row) {
                $values[$k1][$row->kriteria_2_id] = $row->nilai_perbandingan;
            }
        }

        // ====== BUILD $subValues untuk setiap parent ======
        $subValues = [];

        foreach ($parents as $p) {
            foreach ($p->sub as $s1) {
                foreach ($p->sub as $s2) {
                    $v = AhpMatrix::where('expert_id', $expert->id)
                        ->where('kriteria_1_id', $s1->id)
                        ->where('kriteria_2_id', $s2->id)
                        ->value('nilai_perbandingan');

                    if ($v) {
                        $subValues[$p->id][$s1->id][$s2->id] = $v;
                    }
                }
            }
        }

        return view('admin.ahp.experts.matrix', [
            'expert'     => $expert,
            'kriteria'   => $all,
            'parents'    => $parents,
            'values'     => $values,
            'subValues'  => $subValues,
            'existing'   => $existing
        ]);
    }


    public function saveMatrix(Request $r, Expert $expert)
{
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
                    'nilai_perbandingan' => $val
                ]
            );
        }
    }

    return back()->with('success', 'Matrix pakar disimpan.');
}


    public function aggregate(AHPMultiExpertService $svc)
    {
        // contoh: agregasi kriteria utama
        $items = Kriteria::whereNull('parent_id')->get();
        $res = $svc->aggregateMatricesForItems($items);

        // hitung bobot dari numeric matrix
        $weights = $svc->computeWeightsFromNumericMatrix($res['matrix']);

        // simpan aggregated matrix ke DB (expert_id null)
        $svc->saveAggregatedToAhpMatrices($res['matrix'],$res['idIndex']);

        return view('admin.ahp.experts.aggregate', [
            'items'=>$res['items'],
            'matrix'=>$res['matrix'],
            'weights'=>$weights
        ]);
    }
}
