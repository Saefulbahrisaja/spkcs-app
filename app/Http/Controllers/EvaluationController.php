<?php
namespace App\Http\Controllers;

use App\Services\EvaluationPipelineService;

class EvaluationController extends Controller
{
    public function run(EvaluationPipelineService $pipeline)
    {
        $result = $pipeline->runPipeline();

        return response()->json([
            'status' => 'success',
            'result' => $result
        ]);
    }
}
