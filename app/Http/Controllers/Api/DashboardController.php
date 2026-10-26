<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function getStatistics(Request $request)
    {
        $period = $request->query('period', 'week');
        $data = $this->service->getStatisticsData($period);
        return response()->json($data, 200);
    }
}