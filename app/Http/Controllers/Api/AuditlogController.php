<?php

namespace App\Http\Controllers\Api;
use App\Http\Requests\StoreAuditlogRequest;
use App\Http\Resources\AuditlogResource;

use App\Models\Auditlog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AuditlogService;




class AuditlogController extends Controller
{
    protected $auditlogService;

    public function __construct(AuditlogService $auditlogService)
    {
        $this->auditlogService = $auditlogService;
        // Chỉ Admin mới được thêm/sửa/xóa
        $this->middleware(['auth:api','checkrole:Admin'])
             ->only(['store', 'update', 'destroy']);
    }

    public function index()
    {
        $auditlogs = $this->auditlogService->getAll();
        return AuditlogResource::collection($auditlogs);
    }

    public function store(StoreAuditlogRequest $request)
    {
        $auditlog = $this->auditlogService->create($request->validated());
        return new AuditlogResource($auditlog);
    }

    public function show($LogId)
    {
        $auditlog = $this->auditlogService->getById($LogId);
        return new AuditlogResource($auditlog);
    }

    public function update(StoreAuditlogRequest $request, $LogId)
    {
        $auditlog = $this->auditlogService->update($LogId, $request->validated());
        return new AuditlogResource($auditlog);
    }

    public function destroy($LogId)
    {
        $this->auditlogService->delete($LogId);
        return response()->json(['message' => 'auditlog deleted successfully']);
    }
}
