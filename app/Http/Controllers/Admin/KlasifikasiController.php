<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlternatifLahan;
use App\Models\KlasifikasiLahan;
use App\Services\NormalisasiService;

class KlasifikasiController extends Controller
{
    public function proses(NormalisasiService $norm)
    {
        $hasil = $norm->klasifikasi();

        return view('admin.klasifikasi.index', [
            'data' => $hasil
        ]);
    }
}
