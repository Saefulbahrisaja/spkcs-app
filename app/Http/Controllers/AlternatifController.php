<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AlternatifLahan;
use App\Models\Kriteria;
use App\Models\NilaiAlternatif;

class AlternatifController extends Controller
{
    public function index()
    {
        return view('admin.alternatif.index', [
            'data' => AlternatifLahan::all(),
            'alternatifs' => AlternatifLahan::all(),
            'kriteria'    => Kriteria::all(),
            'nilaiAlternatif' => NilaiAlternatif::all()->groupBy('alternatif_id'),
        ]);
    }

    public function create()
    {
        return view('admin.wilayah.create', [
            'data' => AlternatifLahan::all(),
            'alternatifs' => AlternatifLahan::all(),
            'kriteria'    => Kriteria::all(),
            'nilaiAlternatif' => NilaiAlternatif::all()->groupBy('alternatif_id'),
            ]);
    }

    public function formNilai()
    {

        $nilai = NilaiAlternatif::all();

    // Bentuk array agar mudah dipanggil di Blade
        $existing = [];
        foreach ($nilai as $n) {
            $existing[$n->alternatif_id][$n->kriteria_id] = $n->nilai;
        }
        
        return view('admin.alternatif.create', [
            'alternatifs' => AlternatifLahan::all(),
            'kriteria'    => Kriteria::all(),
            'existing'    => $existing,
        ]);
    }

    public function simpanNilai(Request $r)
    {
        foreach ($r->nilai as $altId => $nilaiPerKriteria) {

            foreach ($nilaiPerKriteria as $kriteriaId => $nilai) {

                // Ambil bobot kriteria
                $kriteria = \App\Models\Kriteria::find($kriteriaId);
                $bobot = $kriteria->bobot ?? 0;

                // Hitung skor otomatis
                $skor = $nilai * $bobot;

                // Simpan ke DB
                \App\Models\NilaiAlternatif::updateOrCreate(
                    [
                        'alternatif_id' => $altId,
                        'kriteria_id'   => $kriteriaId
                    ],
                    [
                        'nilai' => $nilai,
                        'skor'  => $skor
                    ]
                );
            }
        }

        return back()->with('success', 'Nilai & skor alternatif berhasil disimpan.');
    }


}
