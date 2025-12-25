<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;

class RoleController extends Controller
{
    protected $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return RoleResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreRoleRequest $request)
    {
        $role = $this->service->create($request->validated());

        return new RoleResource($role);
    }
   //hiện 1
    public function show($RoleId)
    {
        $role = $this->service->find($RoleId);
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        return new RoleResource($role);
    }
  //cập nhật
    public function update(StoreRoleRequest $request, $RoleId)
    {
        $role = $this->service->update($RoleId, $request->validated());
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        return new RoleResource($role);
    }
   //xoá
    public function destroy($RoleId)
    {
        $deleted = $this->service->delete($RoleId);
        if (!$deleted) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        return response()->json(['message' => 'Role deleted successfully']);
    }
}
