<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kriteria;
use App\Models\AhpMatrix;
use Illuminate\Http\Request;
use App\Models\NilaiAlternatif;

class KriteriaController extends Controller
{
    public function index()
    {
        $matrix = AhpMatrix::all();
        $subKriteria = NilaiAlternatif::select('atribut_nama')
                    ->distinct()
                    ->orderBy('atribut_nama')
                    ->pluck('atribut_nama');
        $hasil = app(\App\Services\AHPService::class)->hitungBobot();
        $subValues = [];

        foreach (Kriteria::whereNull('parent_id')->get() as $parent) {
            foreach ($parent->sub as $s1) {
                foreach ($parent->sub as $s2) {
                    $subValues[$parent->id][$s1->id][$s2->id] =
                        AhpMatrix::where('kriteria_1_id',$s1->id)
                                ->where('kriteria_2_id',$s2->id)
                                ->value('nilai_perbandingan');
                }
            }
        }

        return view('admin.kriteria.index', [
            'kriteria' => Kriteria::all(),
            'values' => $matrix->groupBy('kriteria_1_id')->map->pluck('nilai_perbandingan','kriteria_2_id'),
            'hasil' => $hasil,
            'subValues' => $subValues,
            'subKriteria' => $subKriteria
        ]);
    }
    
    public function create()
    {
        $subKriteria = NilaiAlternatif::select('atribut_nama')
                    ->distinct()
                    ->orderBy('atribut_nama')
                    ->pluck('atribut_nama');

        return view('admin.kriteria.create', [
            'subKriteria' => $subKriteria
        ]);
    }
    
    public function store(Request $request)
{
    $request->validate([
        'nama_kriteria' => 'required|string',
        'tipe'          => 'required',
        'parent_id'     => 'nullable|exists:kriterias,id',
    ]);

    $nameLower = trim(strtolower($request->nama_kriteria));

    // Cek duplikasi
    $exists = Kriteria::whereRaw('LOWER(nama_kriteria) = ?', [$nameLower])
                        ->where('parent_id', $request->parent_id)
                        ->exists();

    if ($exists) {
        return back()->with('error', 'Nama kriteria sudah ada dalam level tersebut.');
    }

    // SIMPAN KRITERIA BARU
    $kriteria = Kriteria::create([
        'nama_kriteria' => $request->nama_kriteria,
        'tipe'          => $request->tipe,
        'parent_id'     => $request->parent_id,
        'bobot'         => 0,
        'bobot_global'  => 0,
    ]);

    $attrName = strtolower($kriteria->nama_kriteria);

    // Ambil semua nilai alternatif yang atribut_nama sama (ignoring case)
    $rows = NilaiAlternatif::whereRaw("LOWER(atribut_nama) = ?", [$attrName])->get();

    foreach ($rows as $row) {

        // Jika baris sudah punya kriteria_id, jangan sentuh
        if ($row->kriteria_id !== null) continue;

        // Update baris nilai yg sudah ada
        $row->update([
            'kriteria_id' => $kriteria->id
        ]);
    }

    return redirect()->route('admin.ahp.experts')
                     ->with('success', 'Kriteria berhasil ditambahkan & nilai alternatif diperbarui!');
}


    public function edit($id)
    {
        return view('admin.kriteria.edit', [
            'kriteria' => Kriteria::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_kriteria' => 'required|string',
            'tipe'          => 'required',
            'parent_id'     => 'nullable|exists:kriterias,id',
        ]);

        $name = trim(strtolower($request->nama_kriteria));

        // Cek duplikasi selain dirinya sendiri
        $exists = Kriteria::whereRaw('LOWER(nama_kriteria) = ?', [$name])
                            ->where('parent_id', $request->parent_id)
                            ->where('id', '!=', $id)
                            ->exists();

        if ($exists) {
            return back()->with('error', 'Nama sub kriteria sudah ada dalam level tersebut.');
        }

        Kriteria::findOrFail($id)->update([
            'nama_kriteria' => $request->nama_kriteria,
            'tipe'          => $request->tipe,
            'parent_id'     => $request->parent_id
        ]);

        return redirect()->route('admin.ahp.experts')
                        ->with('success','Kriteria berhasil diupdate!');
    }


    public function destroy($id)
    {
        Kriteria::destroy($id);
        return back();
    }
}
