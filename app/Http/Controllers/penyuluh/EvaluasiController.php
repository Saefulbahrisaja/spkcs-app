<?php

namespace App\Http\Controllers\Penyuluh;

use App\Http\Controllers\Controller;
use App\Models\AlternatifLahan;

class EvaluasiController extends Controller
{
    public function index()
    {
        $data = AlternatifLahan::with(['klasifikasi','vikor'])->get();
        return view('penyuluh.evaluasi.index', compact('data'));
    }
}
