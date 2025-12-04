<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expert;
use App\Models\AhpMatrix;
use App\Models\Kriteria;
use Illuminate\Http\Request;

class ExpertAHPController extends Controller
{
    /**
     * LIST semua pakar & pilih salah satu
     */
    public function index()
    {
        return view('admin.expert_ahp.index', [
            'experts'   => Expert::all(),
            'kriteria'  => Kriteria::whereNull('parent_id')->get()
        ]);
    }


    /**
     * FORM input AHP untuk PAKAR tertentu
     */
    public function form($expert_id)
    {
        $expert = Expert::findOrFail($expert_id);
        $kriteria = Kriteria::whereNull('parent_id')->get();

        // Ambil matrix yang pernah diisi pakar ini
        $values = [];
        foreach ($kriteria as $k1) {
            foreach ($kriteria as $k2) {

                $values[$k1->id][$k2->id] =
                    AhpMatrix::where('expert_id', $expert_id)
                    ->where('kriteria_1_id', $k1->id)
                    ->where('kriteria_2_id', $k2->id)
                    ->value('nilai_perbandingan');
            }
        }

        return view('admin.expert_ahp.form', compact('expert','kriteria','values'));
    }


    /**
     * SIMPAN MATRIX pairwise comparison untuk PAKAR tertentu
     */
    public function save(Request $request, $expert_id)
    {
        if (!$request->matrix) {
            return back()->with('error', 'Matrix kosong.');
        }

        // Hapus data lama
        AhpMatrix::where('expert_id', $expert_id)->delete();

        foreach ($request->matrix as $k1 => $row) {
            foreach ($row as $k2 => $val) {

                if ($val === null || $val === '') continue;

                AhpMatrix::create([
                    'expert_id'      => $expert_id,
                    'kriteria_1_id'  => $k1,
                    'kriteria_2_id'  => $k2,
                    'nilai_perbandingan' => (float)$val
                ]);
            }
        }

        return back()->with('success', 'Matrix AHP pakar berhasil disimpan.');
    }


    /**
     * TAMBAH PAKAR 
     */
    public function addExpert(Request $r)
    {
        $expert = Expert::create([
            'name'   => $r->name,
            'weight' => $r->weight ?? 1
        ]);

        return redirect()
            ->route('expert.ahp.form', $expert->id)
            ->with('success', 'Pakar ditambahkan.');

    }
}
