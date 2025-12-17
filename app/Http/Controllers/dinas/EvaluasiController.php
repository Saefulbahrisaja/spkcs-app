<?php

namespace App\Http\Controllers\Dinas;

use App\Http\Controllers\Controller;
use App\Models\AlternatifLahan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class EvaluasiController extends Controller
{
    public function index()
    {
        $data = AlternatifLahan::with([
            'nilaiAlternatif',
            'klasifikasi'
        ])->orderBy('lokasi')->get();


        return view('dinas.evaluasi.index', compact('data'));
    }

    public function show($id)
    {
        
        $wilayah = AlternatifLahan::with([
            'nilaiAlternatif',
            'klasifikasi'
        ])->findOrFail($id);

        return view('dinas.evaluasi.show', compact('wilayah'));
    }

    public function validasi(Request $request, $id)
    {
        $wilayah = AlternatifLahan::findOrFail($id);

        // ====== LOCK TOTAL JIKA SUDAH DISETUJUI ======
        if ($wilayah->status_validasi === 'disetujui') {
            return redirect()
                ->back()
                ->with('error', 'Data sudah disetujui dan tidak dapat diubah.');
        }

        $request->validate([
            'status_validasi' => 'required|in:disetujui,perlu_revisi',
            'rekomendasi'     => 'nullable|string'
        ]);

        $wilayah->update([
            'status_validasi'    => $request->status_validasi,
            'rekomendasi_dinas'  => $request->rekomendasi
        ]);

        return redirect()
            ->route('dinas.evaluasi')
            ->with('success', 'Evaluasi berhasil disimpan.');
    }

    public function generate()
    {
        $data = AlternatifLahan::with([
            'nilaiAlternatif',
            'klasifikasi'
        ])->get();

        // ===== SUMMARY OTOMATIS =====
        $summary = [
            'S1' => [],
            'S2' => [],
            'S3' => [],
        ];

        foreach ($data as $d) {
            if (in_array($d->kelas_kesesuaian, ['S1','S2','S3'])) {
                $summary[$d->kelas_kesesuaian][] = $d->lokasi;
            }
        }

        // ubah ke string siap tampil
        $summaryText = [
            'S1' => implode(', ', $summary['S1']),
            'S2' => implode(', ', $summary['S2']),
            'S3' => implode(', ', $summary['S3']),
        ];

        // ===== AMBIL REKOMENDASI KEBIJAKAN TERBARU =====
        $kebijakan = \App\Models\RekomendasiKebijakan::orderByDesc('tanggal')->first();

        $pdf = Pdf::loadView('dinas.evaluasi.pdf', [
            'data'        => $data,
            'tanggal'     => now()->format('d F Y'),
            'summary'     => $summaryText,
            'kebijakan'   => $kebijakan
        ])->setPaper('A4', 'portrait');

        return $pdf->download('Laporan_Evaluasi_Kesesuaian_Lahan_Resmi.pdf');
    }


    public function generatePerWilayah($id)
    {
        $wilayah = AlternatifLahan::with([
            'nilaiAlternatif',
            'klasifikasi'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('dinas.evaluasi.pdf_wilayah', [
            'w' => $wilayah,
            'tanggal' => now()->translatedFormat('d F Y')
        ])->setPaper('A4', 'portrait');

        return $pdf->download(
            'Laporan_Evaluasi_' . str_replace(' ', '_', $wilayah->lokasi) . '.pdf'
        );
    }
}
