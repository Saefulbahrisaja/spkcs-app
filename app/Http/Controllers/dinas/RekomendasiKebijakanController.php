<?php

namespace App\Http\Controllers\dinas;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiKebijakan;
use Illuminate\Http\Request;
use App\Models\AlternatifLahan;

class RekomendasiKebijakanController extends Controller
{
    public function index()
    {
        $data = RekomendasiKebijakan::orderByDesc('tanggal')->get();
        return view('dinas.rekomendasi.index', compact('data'));
    }

   public function create()
    {
       $wilayahS1 = AlternatifLahan::whereHas('klasifikasi', function ($q) {
            $q->where('kelas_kesesuaian', 'S1');
        })
        ->orderBy('lokasi')
        ->pluck('lokasi')
        ->toArray();

    $wilayahS2 = AlternatifLahan::whereHas('klasifikasi', function ($q) {
            $q->where('kelas_kesesuaian', 'S2');
        })
        ->orderBy('lokasi')
        ->pluck('lokasi')
        ->toArray();

    $wilayahS3 = AlternatifLahan::whereHas('klasifikasi', function ($q) {
            $q->where('kelas_kesesuaian', 'S3');
        })
        ->orderBy('lokasi')
        ->pluck('lokasi')
        ->toArray();

    $wilayahPrioritas = "";

    if (!empty($wilayahS1)) {
        $wilayahPrioritas .= "S1 (Prioritas Utama):\n- " . implode("\n- ", $wilayahS1) . "\n\n";
    }

    if (!empty($wilayahS2)) {
        $wilayahPrioritas .= "S2 (Prioritas Menengah):\n- " . implode("\n- ", $wilayahS2) . "\n\n";
    }

    if (!empty($wilayahS3)) {
        $wilayahPrioritas .= "S3 (Prioritas Terbatas):\n- " . implode("\n- ", $wilayahS3);
    }

    return view('dinas.rekomendasi.create', [
        'wilayahPrioritas' => trim($wilayahPrioritas)
    ]);
}


    public function store(Request $request)
    {
        $request->validate([
            'tanggal'            => 'required|date',
            'wilayah_prioritas'  => 'required|string',
            'daftar_intervensi'  => 'required|string',
            'catatan'            => 'nullable|string',
            'status'             => 'required|in:draft,ditetapkan,ditunda'
        ]);

        RekomendasiKebijakan::create($request->all());

        return redirect()
            ->route('dinas.kebijakan.index')
            ->with('success', 'Rekomendasi kebijakan berhasil disimpan');
    }
}
