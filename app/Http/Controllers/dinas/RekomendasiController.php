<?php

namespace App\Http\Controllers\Dinas;

use App\Http\Controllers\Controller;
use App\Models\RekomendasiKebijakan;
use App\Models\LaporanEvaluasi;
use Illuminate\Http\Request;
use App\Models\AlternatifLahan;

class RekomendasiController extends Controller
{
    public function index()
    {
        // return view('dinas.rekomendasi.index', [
        //     'data' => RekomendasiKebijakan::latest()->get()
        // ]);
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
        //return redirect()->route('dinas.rekomendasi.index');
    }

    public function edit($id)
    {
        return view('dinas.rekomendasi.edit', [
            'data' => RekomendasiKebijakan::findOrFail($id)
        ]);
    }

    public function update(Request $request, $id)
    {
        // RekomendasiKebijakan::findOrFail($id)->update($request->all());
        // return redirect()->route('dinas.rekomendasi.index');
    }

    public function byAlternatif($id)
    {
        $alt = AlternatifLahan::select(
                'id',
                'lokasi',
                //'kelas_kesesuaian',
                'status_validasi',
                'rekomendasi_dinas'
            )
            ->findOrFail($id);

        return response()->json([
            'lokasi' => $alt->lokasi,
            'kelas'  => $alt->kelas_kesesuaian,
            'status' => $alt->status_validasi,
            'rekomendasi' => $alt->rekomendasi_dinas
                ?: $this->fallbackRekomendasi($alt->kelas_kesesuaian)
        ]);
    }

    /** fallback kalau dinas belum isi */
    private function fallbackRekomendasi($kelas)
    {
        return match ($kelas) {
            'S1' => 'Sangat direkomendasikan untuk pengembangan pertanian.',
            'S2' => 'Direkomendasikan dengan penyesuaian teknis tertentu.',
            'S3' => 'Kurang direkomendasikan, perlu perbaikan lahan.',
            default => 'Tidak direkomendasikan untuk penggunaan saat ini.'
        };
    }
}
