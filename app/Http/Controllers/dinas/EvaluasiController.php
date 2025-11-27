<?php

namespace App\Http\Controllers\Dinas;

use App\Http\Controllers\Controller;
use App\Models\AlternatifLahan;

class EvaluasiController extends Controller
{
    public function index()
    {
        $alternatif = AlternatifLahan::with(['klasifikasi','vikor'])->get();
        return view('dinas.evaluasi.index', compact('alternatif'));
    }
}
