<?php

namespace App\Http\Controllers\Dinas;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiKebijakan;
use App\Models\LaporanEvaluasi;
use Illuminate\Http\Request;

class RekomendasiController extends Controller
{
    public function index()
    {
        return view('dinas.rekomendasi.index', [
            'data' => RekomendasiKebijakan::latest()->get()
        ]);
    }

    public function create()
    {
        return view('dinas.rekomendasi.create', [
            'laporan' => LaporanEvaluasi::all()
        ]);
    }

    public function store(Request $request)
    {
        RekomendasiKebijakan::create($request->all());
        return redirect()->route('dinas.rekomendasi.index');
    }

    public function edit($id)
    {
        return view('dinas.rekomendasi.edit', [
            'data' => RekomendasiKebijakan::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        RekomendasiKebijakan::findOrFail($id)->update($request->all());
        return redirect()->route('dinas.rekomendasi.index');
    }
}
