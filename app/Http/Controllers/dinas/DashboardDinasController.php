<?php

namespace App\Http\Controllers\Dinas;

use App\Http\Controllers\Controller;
use App\Models\AlternatifLahan;
use Illuminate\Support\Facades\DB;

class DashboardDinasController extends Controller
{
    public function index()
    {
        // ===============================
        // 1. RINGKASAN JUMLAH WILAYAH
        // ===============================
        $ringkasan = AlternatifLahan::select(
                'klasifikasi_lahans.kelas_kesesuaian',
                DB::raw('COUNT(alternatif_lahans.id) as total')
            )
            ->join('klasifikasi_lahans','klasifikasi_lahans.alternatif_id','=','alternatif_lahans.id')
            ->groupBy('klasifikasi_lahans.kelas_kesesuaian')
            ->pluck('total','kelas_kesesuaian');

        // ===============================
        // 2. RINGKASAN WILAYAH PRIORITAS
        // ===============================
        $wilayahPrioritas = AlternatifLahan::whereHas('klasifikasi', function($q){
                $q->whereIn('kelas_kesesuaian',['S1','S2','S3']);
            })
            ->with('klasifikasi')
            ->orderBy('lokasi')
            ->get();

        return view('dinas.dashboard', compact(
            'ringkasan',
            'wilayahPrioritas'
        ));
    }
}
