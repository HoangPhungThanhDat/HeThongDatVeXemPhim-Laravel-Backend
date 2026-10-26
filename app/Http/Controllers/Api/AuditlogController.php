<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    protected $service;

    public function __construct(AuditLogService $service)
    {
        $this->service = $service;
    }

    public function getLogs(Request $request): JsonResponse
    {
        $search = $request->query('search');
        $action = $request->query('action');
        $range = $request->query('range', '7 days');

        // Gọi Service xử lý toàn bộ logic
        $data = $this->service->getStatisticsData($search, $action, $range);

        return response()->json($data, 200);
    }
}