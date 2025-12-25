<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Resources\StaffResource;
use App\Services\StaffService;

class StaffController extends Controller
{
    protected $service;

    public function __construct(StaffService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return StaffResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreStaffRequest $request)
    {
        $staff = $this->service->create($request->validated());

        return new StaffResource($staff);
    }
   //hiện 1
    public function show($StaffId)
    {
        $staff = $this->service->find($StaffId);
        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }
        return new StaffResource($staff);
    }
  //cập nhật
    public function update(StoreStaffRequest $request, $StaffId)
    {
        $staff = $this->service->update($StaffId, $request->validated());
        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }
        return new StaffResource($staff);
    }
   //xoá
    public function destroy($StaffId)
    {
        $deleted = $this->service->delete($StaffId);
        if (!$deleted) {
            return response()->json(['message' => 'Staff not found'], 404);
        }
        return response()->json(['message' => 'Staff deleted successfully']);
    }
}
