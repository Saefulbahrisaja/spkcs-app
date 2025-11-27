<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kriteria;
use Illuminate\Http\Request;

class KriteriaController extends Controller
{
    public function index()
    {
        return view('admin.kriteria.index', [
            'kriteria' => Kriteria::all()
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
