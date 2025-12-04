<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kriteria;
use App\Models\AhpMatrix;
use Illuminate\Http\Request;

class KriteriaController extends Controller
{
    public function index()
    {
        $matrix = AhpMatrix::all();
        $kriteria = Kriteria::all();
        $hasil = app(\App\Services\AHPService::class)->hitungBobot();
        return view('admin.kriteria.index', [
            'kriteria' => Kriteria::all(),
            'values' => $matrix->groupBy('kriteria_1_id')->map->pluck('nilai_perbandingan','kriteria_2_id'),
            'hasil' => $hasil
        ]);

        
    }
    

    public function create()
    {
        return view('admin.kriteria.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kriteria' => 'required',
            'tipe' => 'required'
        ]);

        Kriteria::create($request->all());

        return redirect()->route('admin.kriteria.index');
    }

    public function edit($id)
    {
        return view('admin.kriteria.edit', [
            'kriteria' => Kriteria::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        Kriteria::findOrFail($id)->update($request->all());
        return redirect()->route('admin.kriteria.index');
    }

    public function destroy($id)
    {
        Kriteria::destroy($id);
        return back();
    }
}
