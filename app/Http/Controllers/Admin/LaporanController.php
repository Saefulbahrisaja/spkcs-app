<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LaporanEvaluasi;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index()
    {
        return view('admin.laporan.index', [
            'data' => LaporanEvaluasi::latest()->get()
        ]);
    }

    public function create()
    {
        return view('admin.laporan.create');
    }

    public function store(Request $request)
    {
        LaporanEvaluasi::create($request->all());
        return redirect()->route('admin.laporan.index');
    }

    public function show($id)
    {
        return view('admin.laporan.show', [
            'laporan' => LaporanEvaluasi::findOrFail($id)
        ]);
    }

    public function publish($id)
    {
        $laporan = LaporanEvaluasi::findOrFail($id);
        $laporan->update(['status_draft' => 'published']);

        return back();
    }
}
