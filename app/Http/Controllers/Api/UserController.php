<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
        $this->middleware('auth:api')->only(['updateProfile', 'getProfile']);
    }

    // Lấy tất cả
    public function index()
    {
        return UserResource::collection($this->service->getAll());
    }

    // ✅ Phân trang server-side
    public function getPaged(Request $request)
    {
        $page   = max(1, (int) $request->query('page',  1));
        $limit  = min(100, max(1, (int) $request->query('limit', 10)));
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = \App\Models\User::with('role');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('FullName',      'like', "%{$search}%")
                  ->orWhere('Email',       'like', "%{$search}%")
                  ->orWhere('PhoneNumber', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('Status', $status);
        }

        $total      = $query->count();
        $totalPages = (int) ceil($total / $limit);
        $items      = $query->orderBy('UserId', 'desc')
                            ->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->get();

        return response()->json([
            'data'       => UserResource::collection($items),
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => $totalPages,
        ]);
    }

    // Thêm
    public function store(StoreUserRequest $request)
    {
        $user = $this->service->create($request->validated());
        return new UserResource($user);
    }

    // Hiện 1
    public function show($UserId)
    {
        $user = $this->service->find($UserId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return new UserResource($user);
    }

    // Cập nhật (Admin only)
    public function update(StoreUserRequest $request, $UserId)
    {
        $user = $this->service->update($UserId, $request->validated());
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return new UserResource($user);
    }

    // Xoá
    public function destroy($UserId)
    {
        $deleted = $this->service->delete($UserId);
        if (!$deleted) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json(['message' => 'User deleted successfully']);
    }

    // ===== API CHO KHÁCH HÀNG =====

    public function getProfile()
    {
        $user = Auth::user();
        return new UserResource($user);
    }

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

            $existingEmail = \App\Models\User::where('Email', $request->Email)
                ->where('UserId', '!=', $userId)
                ->first();

            if ($existingEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email đã được sử dụng',
                    'errors'  => ['Email' => ['Email đã được sử dụng']]
                ], 422);
            }

            $existingPhone = \App\Models\User::where('PhoneNumber', $request->PhoneNumber)
                ->where('UserId', '!=', $userId)
                ->first();

            if ($existingPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số điện thoại đã được sử dụng',
                    'errors'  => ['PhoneNumber' => ['Số điện thoại đã được sử dụng']]
                ], 422);
            }

            $data = $request->only([
                'FullName', 'Email', 'PhoneNumber',
                'Address', 'DateOfBirth', 'Gender'
            ]);

            $updatedUser = $this->service->updateProfile($userId, $data);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin thành công',
                'data'    => new UserResource($updatedUser)
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật thất bại: ' . $e->getMessage()
            ], 500);
        }
    }

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