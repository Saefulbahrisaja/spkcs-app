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
            'data' => AlternatifLahan::all()
        ]);
    }

    public function create()
    {
        return view('admin.wilayah.create');
    }

        public function formNilai()
    {
        return view('admin.alternatif.create', [
            'alternatifs' => AlternatifLahan::all(),
            'kriteria'    => Kriteria::all()
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
