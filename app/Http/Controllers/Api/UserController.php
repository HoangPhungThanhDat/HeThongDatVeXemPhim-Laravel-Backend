<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return UserResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreUserRequest $request)
    {
        $user = $this->service->create($request->validated());

        return new UserResource($user);
    }
   //hiện 1
    public function show($UserId)
    {
        $user = $this->service->find($UserId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return new UserResource($user);
    }
  //cập nhật
    public function update(StoreUserRequest $request, $UserId)
    {
        $user = $this->service->update($UserId, $request->validated());
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return new UserResource($user);
    }
   //xoá
    public function destroy($UserId)
    {
        $deleted = $this->service->delete($UserId);
        if (!$deleted) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['message' => 'User deleted successfully']);
    }
}
