<?php
namespace App\Services;

use App\Services\SFAHPService;
use App\Services\ScoringService;
use App\Services\KlasifikasiService;
use App\Services\VIKORService;
use App\Models\Kriteria;
use App\Models\LaporanEvaluasi;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class EvaluationPipelineService
{
    protected SFAHPService $sfahp;
    protected ScoringService $scoring;
    protected KlasifikasiService $klasifikasi;
    protected VikorService $vikor;

    public function __construct(
        SFAHPService $sfahp,
        ScoringService $scoring,
        KlasifikasiService $klasifikasi,
        VikorService $vikor
    ) {
        $this->sfahp = $sfahp;
        $this->scoring = $scoring;
        $this->klasifikasi = $klasifikasi;
        $this->vikor = $vikor;
    }

    /**
     * JALANKAN PIPELINE LENGKAP (Multi Expert)
     */
    public function runPipeline(): array
    {
        DB::beginTransaction();
        try {
            /* ==============================================
             * 1. SF-AHP MULTI EXPERT → Bobot kriteria
             * ============================================== */
            $parents = Kriteria::whereNull('parent_id')->get()->values();
            $parentAgg = $this->sfahp->aggregateAndCompute($parents);

            $parentWeights = $parentAgg['weights'];
            $this->sfahp->saveWeightsToKriteria($parents, $parentWeights);

            /* SUB-KRITERIA */
            $global = [];
            $local = [];

            foreach ($parents as $idx => $parent) {
                $subs = Kriteria::where('parent_id', $parent->id)->get()->values();

                if ($subs->count() > 1) {
                    $subAgg = $this->sfahp->aggregateAndCompute($subs);
                    foreach ($subs as $j => $s) {
                        $local[$s->id]  = $subAgg['weights'][$j];
                        $global[$s->id] = $subAgg['weights'][$j] * $parentWeights[$idx];
                    }
                }
            }

            $this->sfahp->saveSubCriteriaWeights($local, $global);

            $this->scoring->hitungSkorAlternatif();

            $klasResult = $this->klasifikasi->prosesKlasifikasi();

            $vikorResult = $this->vikor->prosesVikor();

            $laporan = LaporanEvaluasi::create([
                'tanggal'           => Carbon::now(),
                'hasil_klasifikasi' => json_encode($klasResult),
                'hasil_ranking'     => json_encode($vikorResult),
                'status_draft'      => 1
            ]);

            DB::commit();

            return [
                'bobot_kriteria' => $parentWeights,
                'bobot_global_sub' => $global,
                'klasifikasi' => $klasResult,
                'ranking' => $vikorResult,
                'laporan_id' => $laporan->id
            ];

        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
