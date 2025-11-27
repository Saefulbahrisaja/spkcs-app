<?php

namespace App\Http\Controllers;

use App\Models\AlternatifLahan;

class GISController extends Controller
{
    public function index()
    {
        $data = AlternatifLahan::select('id', 'lokasi', 'kelas_kesesuaian', 'geojson_path')->get();

        return view('gis.index', compact('data'));
    }

    public function publicPeta()
    {
        return $this->index();
    }
}
