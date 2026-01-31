<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
        $this->middleware('auth:api')->only(['updateProfile', 'getProfile']);
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

    //cập nhật (Admin only)
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

    // ===== API CHO KHÁCH HÀNG =====
    
    /**
     * Lấy thông tin cá nhân của user đang đăng nhập
     */
    public function getProfile()
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    /**
     * Cập nhật thông tin cá nhân của user đang đăng nhập
     */
    public function updateProfile(UpdateProfileRequest $request)
    {
        try {
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin đăng nhập'
                ], 401);
            }
            
            $userId = $user->UserId;
            
            // Validate unique
            $existingEmail = \App\Models\User::where('Email', $request->Email)
                ->where('UserId', '!=', $userId)
                ->first();
                
            if ($existingEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email đã được sử dụng',
                    'errors' => ['Email' => ['Email đã được sử dụng']]
                ], 422);
            }
            
            $existingPhone = \App\Models\User::where('PhoneNumber', $request->PhoneNumber)
                ->where('UserId', '!=', $userId)
                ->first();
                
            if ($existingPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số điện thoại đã được sử dụng',
                    'errors' => ['PhoneNumber' => ['Số điện thoại đã được sử dụng']]
                ], 422);
            }
            
            $data = $request->only([
                'FullName',
                'Email', 
                'PhoneNumber',
                'Address',
                'DateOfBirth',
                'Gender'
            ]);
    
            $updatedUser = $this->service->updateProfile($userId, $data);
            
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công',
                'data' => new UserResource($updatedUser)
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thất bại: ' . $e->getMessage()
            ], 500);
        }
    }



      /**
     * Đổi mật khẩu cho user đang đăng nhập
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $user = Auth::guard('api')->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy thông tin đăng nhập'
                ], 401);
            }

            $result = $this->service->changePassword(
                $user->UserId,
                $request->current_password,
                $request->new_password
            );

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => $result['message']
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đổi mật khẩu thất bại: ' . $e->getMessage()
            ], 500);
        }
    }
}