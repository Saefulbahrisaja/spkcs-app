<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\EvaluationPipelineService;
use Illuminate\Http\Request;

class EvaluationPipelineController extends Controller
{
    public function run(EvaluationPipelineService $pipeline)
    {
        try {
            $result = $pipeline->runPipeline();
            return redirect()
                ->back()
                ->with('success', "Pipeline berhasil diproses! Laporan ID: {$result['laporan_id']}");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', "Gagal menjalankan pipeline: " . $e->getMessage());
        }
    }
}
