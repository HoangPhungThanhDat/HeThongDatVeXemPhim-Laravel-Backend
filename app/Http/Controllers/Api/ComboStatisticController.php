<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ComboStatisticService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ComboStatisticController extends Controller
{
    protected $service;

    public function __construct(ComboStatisticService $service)
    {
        $this->service = $service;
    }

    public function getComboStatistics(Request $request): JsonResponse
    {
        $period = $request->query('period', '7days');
        
        $data = $this->service->getComboStatistics($period);

        return response()->json($data, 200);
    }
}