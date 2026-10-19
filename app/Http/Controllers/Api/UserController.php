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

/**
 * @group Users
 * 
 * APIs for managing users.
 */
class UserController extends Controller
{
    protected $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
        $this->middleware('auth:api')->only(['updateProfile', 'getProfile']);
    }

    /**
     * Get all users
     * 
     * Get a list of all users. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "UserId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "admin@gauphim.com",
     *     "PhoneNumber": "0987654321",
     *     "Status": "Active",
     *     "RoleId": 1
     *   }
     * ]
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Forbidden - Admin only"
     * }
     */
    public function index()
    {
        return UserResource::collection($this->service->getAll());
    }

    /**
     * Get paginated users
     * 
     * Get a paginated list of users with search and filter options. (Admin only)
     * 
     * @authenticated
     * 
     * @queryParam page int The page number. Default: 1. Example: 1
     * @queryParam limit int Number of items per page. Default: 10. Max: 100. Example: 10
     * @queryParam search string Search by name, email, or phone number. Example: Nguyen
     * @queryParam status string Filter by status. Enum: Active, Inactive, Banned. Example: Active
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "UserId": 1,
     *       "FullName": "Nguyễn Văn A",
     *       "Email": "admin@gauphim.com",
     *       "PhoneNumber": "0987654321"
     *     }
     *   ],
     *   "total": 100,
     *   "page": 1,
     *   "limit": 10,
     *   "totalPages": 10
     * }
     */
    public function getPaged(Request $request)
    {
        $page   = max(1, (int) $request->query('page', 1));
        $limit  = min(100, max(1, (int) $request->query('limit', 10)));
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        // ✅ Gọi xuống Service -> Repository
        $result = $this->service->getPaged($page, $limit, $search, $status);

        return response()->json([
            'data'       => UserResource::collection($result['data']),
            'total'      => $result['total'],
            'page'       => $result['page'],
            'limit'      => $result['limit'],
            'totalPages' => $result['totalPages'],
        ]);
    }

    /**
     * Create a new user
     * 
     * Create a new user with the provided data. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam FullName string required The full name. Example: Nguyễn Văn B
     * @bodyParam Email string required The email address. Example: user2@example.com
     * @bodyParam Password string required The password. Example: 123456
     * @bodyParam PhoneNumber string required The phone number. Example: 0987654321
     * @bodyParam RoleId int required The role ID. Example: 2
     * @bodyParam Gender string The gender. Enum: Male, Female, Other. Example: Male
     * @bodyParam DateOfBirth string The date of birth. Example: 2000-01-01
     * @bodyParam Address string The address. Example: Hà Nội
     * 
     * @response 201 {
     *   "UserId": 2,
     *   "FullName": "Nguyễn Văn B",
     *   "Email": "user2@example.com",
     *   "PhoneNumber": "0987654321",
     *   "RoleId": 2
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Email": ["The email has already been taken."]
     *   }
     * }
     */
    public function store(StoreUserRequest $request)
    {
        $user = $this->service->create($request->validated());
        return new UserResource($user);
    }

    /**
     * Get a user by ID
     * 
     * Get detailed information about a specific user. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam UserId int required The ID of the user. Example: 1
     * 
     * @response 200 {
     *   "UserId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "Email": "admin@gauphim.com",
     *   "PhoneNumber": "0987654321",
     *   "Gender": "Male",
     *   "DateOfBirth": "2000-01-01",
     *   "Address": "Hà Nội",
     *   "Status": "Active",
     *   "RoleId": 1
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
    public function show($UserId)
    {
        $user = $this->service->find($UserId);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return new UserResource($user);
    }

    /**
     * Update a user
     * 
     * Update a user's information. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam UserId int required The ID of the user. Example: 1
     * 
     * @bodyParam FullName string The full name. Example: Nguyễn Văn A Updated
     * @bodyParam Email string The email address. Example: admin@example.com
     * @bodyParam PhoneNumber string The phone number. Example: 0987654321
     * @bodyParam RoleId int The role ID. Example: 1
     * @bodyParam Status string The status. Enum: Active, Inactive, Banned. Example: Active
     * 
     * @response 200 {
     *   "UserId": 1,
     *   "FullName": "Nguyễn Văn A Updated",
     *   "Email": "admin@example.com",
     *   "Status": "Active"
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
    public function update(StoreUserRequest $request, $UserId)
    {
        $user = $this->service->update($UserId, $request->validated());
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return new UserResource($user);
    }

    /**
     * Delete a user
     * 
     * Delete a user from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam UserId int required The ID of the user. Example: 1
     * 
     * @response 200 {
     *   "message": "User deleted successfully"
     * }
     * @response 404 {
     *   "message": "User not found"
     * }
     */
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
     * Get current user profile
     * 
     * Get the profile of the authenticated user.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "UserId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "Email": "admin@gauphim.com",
     *   "PhoneNumber": "0987654321",
     *   "Address": "Hà Nội",
     *   "Gender": "Male",
     *   "DateOfBirth": "2000-01-01",
     *   "Status": "Active",
     *   "RoleId": 1
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function getProfile()
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    /**
     * Update current user profile
     * 
     * Update the profile of the authenticated user.
     * 
     * @authenticated
     * 
     * @bodyParam FullName string The full name. Example: Nguyễn Văn A Updated
     * @bodyParam Email string The email address. Example: user@example.com
     * @bodyParam PhoneNumber string The phone number. Example: 0987654321
     * @bodyParam Address string The address. Example: Hà Nội
     * @bodyParam DateOfBirth string The date of birth. Example: 2000-01-01
     * @bodyParam Gender string The gender. Enum: Male, Female, Other. Example: Male
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Cập nhật thông tin thành công",
     *   "data": {
     *     "UserId": 1,
     *     "FullName": "Nguyễn Văn A Updated",
     *     "Email": "user@example.com"
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Email đã được sử dụng",
     *   "errors": {
     *     "Email": ["Email đã được sử dụng"]
     *   }
     * }
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

            // ✅ Gọi xuống Service -> Repository kiểm tra trùng
            $existingEmail = $this->service->checkEmailExists($request->Email, $userId);
            if ($existingEmail) {
                return response()->json([
                    'success' => false,
                    'message' => 'Email đã được sử dụng',
                    'errors' => ['Email' => ['Email đã được sử dụng']]
                ], 422);
            }

            $existingPhone = $this->service->checkPhoneExists($request->PhoneNumber, $userId);
            if ($existingPhone) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số điện thoại đã được sử dụng',
                    'errors' => ['PhoneNumber' => ['Số điện thoại đã được sử dụng']]
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
     * Change password
     * 
     * Change the password of the authenticated user.
     * 
     * @authenticated
     * 
     * @bodyParam current_password string required The current password. Example: 123456
     * @bodyParam new_password string required The new password. Minimum 6 characters. Example: 12345678
     * @bodyParam new_password_confirmation string required The confirmation of the new password. Example: 12345678
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Đổi mật khẩu thành công"
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Mật khẩu cũ không đúng"
     * }
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