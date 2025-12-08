<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Expert;
use App\Models\Kriteria;
use App\Models\AhpMatrix;
use App\Services\SFAHPService;

class AHPMultiExpertController extends Controller
{
    public function index(SFAHPService $svc)
    {
        
        $experts = Expert::all();
        $kriteria = Kriteria::all();
        // Ambil semua kriteria utama
        $items = Kriteria::whereNull('parent_id')->get();
        // 1. SF-AHP → SF Matrix
        $res = $svc->aggregateAndCompute($items);
        $sf = $res['sf_matrix'];
        $crisp = $res['crisp_matrix'];
        $weights = $res['weights'];
       
        /** ================================
         *  HITUNG BOBOT SUB-KRITERIA
         *  ================================*/
        $globalMap = [];
        $localMap = [];
        foreach ($items as $idx => $parent) {
            $sub = Kriteria::where('parent_id', $parent->id)->get();
            if ($sub->count() < 2) continue;
            // agregasi sub
            $subRes = $svc->aggregateAndCompute($sub);
            $localWeights = $subRes['weights'];
            // simpan bobot lokal
            foreach ($sub as $i => $s) {
                $localMap[$s->id] = $localWeights[$i];
                // bobot global = bobot kriteria * bobot lokal sub
                $globalMap[$s->id] = $weights[$idx] * $localWeights[$i];
            }
        }
       

        $compute  = $svc->computeWeightsFromSfMatrix($sf);

        $crisp   = $compute['crisp_matrix'];
        $weights = $compute['weights'];
        $CI      = $compute['CI'] ?? 0;
        $CR      = $compute['CR'] ?? 0;
        $lambda  = $compute['lambda_max'] ?? 0;

        // GLOBAL WEIGHTS (sudah dihitung sebelumnya)
        $svc->saveSubCriteriaWeights($localMap, $globalMap);
        $experts = Expert::all()->map(function($ex){
                $ex->has_matrix = \App\Models\AhpMatrix::where('expert_id', $ex->id)->exists();
                return $ex;
            });
        return view('admin.ahp.expert-index', [
            'items'       => $items,
            'sf_matrix'   => $sf,
            'experts'     => $experts,
            'kriteria'   => $kriteria,
            'crisp'       => $crisp,
            'weights'     => $weights, 
            'lambda_max'  => $lambda,
            'CI'          => $CI,
            'CR'          => $CR,
            'globalWeights' => collect($globalMap)->map(function($gw, $id) use ($localMap){
                return [
                    'name'   => Kriteria::find($id)->nama_kriteria,
                    'local'  => $localMap[$id] ?? 0,
                    'global' => $gw,
                ];
            })
        ]);
        //return view('admin.ahp.expert-index', compact('experts','kriteria'));
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

    public function inputMatrixForm(Expert $expert)
{
    $all = Kriteria::with('sub')->get();
    $parents = $all->whereNull('parent_id')->values();

    $existing = AhpMatrix::where('expert_id',$expert->id)->get();

    $values = [];
    foreach ($existing as $row){
        $values[$row->kriteria_1_id][$row->kriteria_2_id] = [
            'mu' => $row->mu ?? 1,
            'nu' => $row->nu ?? 0,
            'pi' => $row->pi ?? 0,
        ];
    }

    // Sub matrix
    $subValues = [];
    foreach ($parents as $p){
        foreach ($p->sub as $s1){
            foreach ($p->sub as $s2){

                $row = AhpMatrix::where('expert_id',$expert->id)
                    ->where('kriteria_1_id',$s1->id)
                    ->where('kriteria_2_id',$s2->id)
                    ->first();

                if ($row){
                    $subValues[$p->id][$s1->id][$s2->id] = [
                        'mu'=>$row->mu,
                        'nu'=>$row->nu,
                        'pi'=>$row->pi
                    ];
                }
            }
        }
    }

    return view('admin.ahp.expert-matrix', [
        'expert'=>$expert,
        'kriteria'=>$all,
        'parents'=>$parents,
        'values'=>$values,
        'subValues'=>$subValues
    ]);
}

    public function saveExpertMatrix(Request $r, SFAHPService $svc, Expert $expert)
{
    $matrix = $r->matrix ?? [];
    $sub    = $r->submatrix ?? [];

    // simpan matrix utama
    foreach ($matrix as $k1 => $row) {
        foreach ($row as $k2 => $label) {

            if (!$label) continue;

            $svc->saveFuzzy($expert->id, $k1, $k2, $label);
           
        }
    }

    // simpan sub-kriteria
    foreach ($sub as $parent => $rows) {
        foreach ($rows as $s1 => $cols) {
            foreach ($cols as $s2 => $label) {

                if (!$label) continue;

                $svc->saveFuzzy($expert->id, $s1, $s2, $label);
               
            }
        }
    }

    return back()->with('success', 'Matrix fuzzy tersimpan.');
}

    // ==================================
    //     AGREGASI SF-AHP (MULTI PAKAR)
    // ==================================
   public function aggregateResult(SFAHPService $svc)
{
    $items = Kriteria::whereNull('parent_id')->get();

    // 1. Agregasi SF-AHP utama
    $agg = $svc->aggregateAndCompute($items);

    $sf  = $agg['sf_matrix'];
    $crisp = $agg['crisp_matrix'];
    $weights = $agg['weights'];
    $CI = $agg['CI'];
    $CR = $agg['CR'];
    $lambda = $agg['lambda_max'];

    // SIMPAN BOBOT UTAMA
    $svc->saveWeightsToKriteria($items, $weights);

    // 2. SUB-KRITERIA
    $global = [];
    $local  = [];

    foreach ($items as $idx=>$p) {
        $sub = Kriteria::where('parent_id',$p->id)->get();
        if ($sub->count()<2) continue;

        $res = $svc->aggregateAndCompute($sub);

        foreach($sub as $i=>$s){
            $local[$s->id]  = $res['weights'][$i];
            $global[$s->id] = $weights[$idx] * $res['weights'][$i];
        }
    }

    $svc->saveSubCriteriaWeights($local,$global);

    return back()->with("success","AHP→SF telah dihitung & bobot disimpan. Modul SHP akan otomatis memakai bobot ini.");
}


}
