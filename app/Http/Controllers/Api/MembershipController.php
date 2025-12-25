<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMembershipRequest;
use App\Http\Resources\MembershipResource;
use App\Services\MembershipService;

class MembershipController extends Controller
{
    protected $service;

    public function __construct(MembershipService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return MembershipResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreMembershipRequest $request)
    {
        $membership = $this->service->create($request->validated());

        return new MembershipResource($membership);
    }
   //hiện 1
    public function show($MembershipId)
    {
        $membership = $this->service->find($MembershipId);
        if (!$membership) {
            return response()->json(['message' => 'Membership not found'], 404);
        }
        return new MembershipResource($membership);
    }
  //cập nhật
    public function update(StoreMembershipRequest $request, $MembershipId)
    {
        $membership = $this->service->update($MembershipId, $request->validated());
        if (!$membership) {
            return response()->json(['message' => 'Membership not found'], 404);
        }
        return new MembershipResource($membership);
    }
   //xoá
    public function destroy($MembershipId)
    {
        $deleted = $this->service->delete($MembershipId);
        if (!$deleted) {
            return response()->json(['message' => 'Membership not found'], 404);
        }
        return response()->json(['message' => 'Membership deleted successfully']);
    }
}
