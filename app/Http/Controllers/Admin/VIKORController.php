<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VIKORService;
use App\Models\PemeringkatanVikor;

class VIKORController extends Controller
{
    
    public function proses(VIKORService $vikor)
    {
        $vikor->calculateVikor();
        return redirect()->route('admin.vikor.hasil');
    }

    public function hasil()
    {
        $data = PemeringkatanVikor::with('alternatif')->orderBy('hasil_ranking')->get();
        return view('admin.vikor.hasil', compact('data'));
    }
}
