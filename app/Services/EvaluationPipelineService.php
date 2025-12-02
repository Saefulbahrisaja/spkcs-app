<?php
namespace App\Services;

use App\Services\AHPService;
use App\Services\ScoringService;
use App\Services\KlasifikasiService;
use App\Services\VikorService;
use App\Models\LaporanEvaluasi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EvaluationPipelineService
{
    protected AHPService $ahp;
    protected ScoringService $scoring;
    protected KlasifikasiService $klas;
    protected VikorService $vikor;

    public function __construct(
        AHPService $ahp,
        ScoringService $scoring,
        KlasifikasiService $klas,
        VikorService $vikor
    ) {
        $this->ahp = $ahp;
        $this->scoring = $scoring;
        $this->klas = $klas;
        $this->vikor = $vikor;
    }

    /**
     * Jalankan pipeline lengkap (transaksional pada bagian penyimpanan ringkasan)
     */
    public function runPipeline(): array
    {
        // 1. AHP -> perbarui bobot kriteria + cek konsistensi
        $ahpResult = $this->ahp->hitungBobot();

        // 2. Scoring (normalisasi + weighted sum)
        $this->scoring->hitungSkorAlternatif();

        // 3. Klasifikasi -> update tabel klasifikasi_lahan
        $klasResult = $this->klas->prosesKlasifikasi();

        // 4. VIKOR -> hitung ranking & simpan pemeringkatan_vikor
        $vikorResult = $this->vikor->prosesVikor();

        // 5. Simpan ringkasan laporan ke laporan_evaluasi
        $laporan = LaporanEvaluasi::create([
            'tanggal' => Carbon::now()->toDateString(),
            'hasil_klasifikasi' => $klasResult,
            'hasil_ranking' => $vikorResult,
            'status_draft' => 'draft'
        ]);

        return [
            'ahp' => $ahpResult,
            'klasifikasi' => $klasResult,
            'vikor' => $vikorResult,
            'laporan_id' => $laporan->id
        ];
    }
}
