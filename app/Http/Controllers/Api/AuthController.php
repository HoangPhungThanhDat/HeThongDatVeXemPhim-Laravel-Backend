<?php

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Http\Requests\StoreRegisterRequest;
// use App\Http\Requests\StoreLoginRequest;
// use App\Http\Resources\UserResource;
// use App\Services\AuthService;
// use Illuminate\Http\JsonResponse;

// class AuthController extends Controller
// {
//     protected $authService;

//     public function __construct(AuthService $authService)
//     {
//         $this->authService = $authService;
//     }

//     public function register(StoreRegisterRequest $request): JsonResponse
//     {
//         $result = $this->authService->register($request->validated());

//         return response()->json([
//             'user'  => new UserResource($result['user']->load('role')),
           
//         ], 201);
//     }

//     public function login(StoreLoginRequest $request): JsonResponse
//     {
//         $result = $this->authService->login(
//             $request->Email,
//             $request->Password
//         );

//         if (!$result) {
//             return response()->json(['error' => 'Invalid credentials'], 401);
//         }

//         return response()->json([
         
//             'token' => $result['token'],
//             'user'  => new UserResource($result['user']),
//         ]);
//     }

//     public function logout(): JsonResponse
//     {
//         $this->authService->logout();
//         return response()->json(['message' => 'Logged out']);
//     }
// }














namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterRequest;
use App\Http\Requests\StoreLoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(StoreRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'user'  => new UserResource($result['user']->load('role')),
        ], 201);
    }

    public function login(StoreLoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->Email,
            $request->Password
        );

        if (!$result) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        return response()->json([
            'token' => $result['token'],
            'user'  => new UserResource($result['user']),
        ]);
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return response()->json(['message' => 'Logged out']);
    }

    // ============================================
    // GOOGLE OAUTH METHODS
    // ============================================

    /**
     * Redirect to Google OAuth
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Google OAuth callback - KIỂM TRA ROLEID = 1 (ADMIN)
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Lấy thông tin user từ Google
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Log thông tin để debug
            Log::info('Google User:', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'id' => $googleUser->getId(),
            ]);

            // ============================================
            // ✅ TÌM USER TRONG DATABASE THEO EMAIL
            // ============================================
            $user = User::where('Email', $googleUser->getEmail())->first();
            
            // ❌ Nếu email chưa đăng ký -> từ chối
            if (!$user) {
                Log::warning('Google login failed - email not registered:', [
                    'email' => $googleUser->getEmail()
                ]);
                
                $error = urlencode('Email chưa được đăng ký. Vui lòng liên hệ quản trị viên để được cấp quyền.');
                return redirect("http://localhost:3000/login?error={$error}");
            }

            // ============================================
            // ✅ KIỂM TRA ROLEID = 1 (ADMIN)
            // ============================================
            $roleId = $user->RoleId;
            
            // Chỉ cho phép RoleId = 1 (Admin)
            if ($roleId != 1) {
                Log::warning('Google login failed - not admin:', [
                    'email' => $googleUser->getEmail(),
                    'role_id' => $roleId
                ]);
                
                $error = urlencode('Tài khoản không có quyền truy cập Admin. Vui lòng liên hệ quản trị viên.');
                return redirect("http://localhost:3000/login?error={$error}");
            }

            // ============================================
            // ✅ KIỂM TRA TRẠNG THÁI TÀI KHOẢN
            // ============================================
            if ($user->Status !== 'Active' && $user->Status !== 'active') {
                $error = urlencode('Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.');
                return redirect("http://localhost:3000/login?error={$error}");
            }

            // ============================================
            // ✅ CẬP NHẬT THÔNG TIN ĐĂNG NHẬP
            // ============================================
            $user->update([
                'UpdatedAt' => now(),
                // Có thể lưu thêm google_id nếu có cột
                // 'google_id' => $googleUser->getId(),
                // 'avatar' => $googleUser->getAvatar(),
            ]);

            // Tạo JWT token
            $token = JWTAuth::fromUser($user);

            Log::info('Google login success (Admin):', [
                'user_id' => $user->UserId,
                'email' => $user->Email,
                'role_id' => $user->RoleId,
                'fullname' => $user->FullName
            ]);

            // 🚀 Redirect về Frontend với token
            return redirect("http://localhost:3000/login?token={$token}");

        } catch (\Exception $e) {
            Log::error('Google login error: ' . $e->getMessage());
            Log::error('Google login trace: ' . $e->getTraceAsString());
            
            $error = urlencode('Đăng nhập Google thất bại: ' . $e->getMessage());
            return redirect("http://localhost:3000/login?error={$error}");
        }
    }

    /**
     * Kiểm tra token Google
     */
    public function checkGoogleLogin(Request $request): JsonResponse
    {
        $token = $request->query('token');
        if (!$token) {
            return response()->json(['error' => 'Token not found'], 401);
        }

        try {
            $user = JWTAuth::setToken($token)->authenticate();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }
            
            return response()->json([
                'success' => true,
                'token' => $token,
                'user' => new UserResource($user->load('role'))
            ]);
        } catch (JWTException $e) {
            Log::error('Check Google token error: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }
}