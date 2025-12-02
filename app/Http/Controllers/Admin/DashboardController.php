<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kriteria;
use App\Models\AlternatifLahan;
use App\Models\LaporanEvaluasi;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'kriteria'    => Kriteria::count(),
            'alternatif'  => AlternatifLahan::count(),
            'laporan'     => LaporanEvaluasi::count(),
        ]);
    }
}
