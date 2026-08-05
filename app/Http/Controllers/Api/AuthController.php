<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterRequest;
use App\Http\Requests\StoreLoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

/**
 * @group Authentication
 * 
 * APIs for user authentication, registration, and Google OAuth login.
 */
class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user
     * 
     * Create a new user account with the provided information.
     * 
     * @unauthenticated
     * 
     * @bodyParam FullName string required The full name of the user. Example: Nguyễn Văn A
     * @bodyParam Email string required The email address. Must be unique. Example: user@example.com
     * @bodyParam Password string required The password. Minimum 6 characters. Example: 123456
     * @bodyParam PhoneNumber string required The phone number. Example: 0987654321
     * @bodyParam Gender string The gender. Enum: Male, Female, Other. Example: Male
     * @bodyParam DateOfBirth string The date of birth. Format: YYYY-MM-DD. Example: 2000-01-01
     * 
     * @response 201 {
     *   "user": {
     *     "UserId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "user@example.com",
     *     "PhoneNumber": "0987654321",
     *     "Gender": "Male",
     *     "DateOfBirth": "2000-01-01",
     *     "Status": "Active",
     *     "RoleId": 2
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Email": ["The email has already been taken."],
     *     "Password": ["The password must be at least 6 characters."]
     *   }
     * }
     */
    public function register(StoreRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'user' => new UserResource($result['user']->load('role')),
        ], 201);
    }

    /**
     * Login to the system
     * 
     * Authenticate a user and return a JWT token.
     * 
     * @unauthenticated
     * 
     * @bodyParam Email string required The email address. Example: admin@gauphim.com
     * @bodyParam Password string required The password. Example: 123456
     * 
     * @response 200 {
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *   "user": {
     *     "UserId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "admin@gauphim.com",
     *     "PhoneNumber": "0987654321",
     *     "RoleId": 1
     *   }
     * }
     * @response 401 {
     *   "error": "Invalid credentials"
     * }
     */
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
            'user' => new UserResource($result['user']),
        ]);
    }

    /**
     * Logout from the system
     * 
     * Invalidate the current JWT token and logout the user.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "message": "Logged out"
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();
        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Redirect to Google OAuth
     * 
     * Redirect the user to Google's OAuth page for authentication.
     * 
     * @unauthenticated
     * 
     * @response 302 {
     *   "message": "Redirecting to Google..."
     * }
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * Handle Google OAuth Callback
     * 
     * Handle the callback from Google OAuth. Only allows users with RoleId = 1 (Admin).
     * 
     * @unauthenticated
     * 
     * @queryParam code string required The OAuth code from Google.
     * @queryParam error string The error message from Google if any.
     * 
     * @response 302 {
     *   "message": "Redirecting to frontend with token"
     * }
     * @response 500 {
     *   "error": "Google login failed"
     * }
     */
    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            Log::info('Google User:', [
                'email' => $googleUser->getEmail(),
                'name' => $googleUser->getName(),
                'id' => $googleUser->getId(),
            ]);

            $user = User::where('Email', $googleUser->getEmail())->first();

            if (!$user) {
                Log::warning('Google login failed - email not registered:', [
                    'email' => $googleUser->getEmail()
                ]);

                $error = urlencode('Email chưa được đăng ký. Vui lòng liên hệ quản trị viên để được cấp quyền.');
                return redirect("http://localhost:3000/login?error={$error}");
            }

            $roleId = $user->RoleId;

            if ($roleId != 1) {
                Log::warning('Google login failed - not admin:', [
                    'email' => $googleUser->getEmail(),
                    'role_id' => $roleId
                ]);

                $error = urlencode('Tài khoản không có quyền truy cập Admin. Vui lòng liên hệ quản trị viên.');
                return redirect("http://localhost:3000/login?error={$error}");
            }

            if ($user->Status !== 'Active' && $user->Status !== 'active') {
                $error = urlencode('Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.');
                return redirect("http://localhost:3000/login?error={$error}");
            }

            $user->update([
                'UpdatedAt' => now(),
            ]);

            $token = JWTAuth::fromUser($user);

            Log::info('Google login success (Admin):', [
                'user_id' => $user->UserId,
                'email' => $user->Email,
                'role_id' => $user->RoleId,
                'fullname' => $user->FullName
            ]);

            return redirect("http://localhost:3000/login?token={$token}");

        } catch (\Exception $e) {
            Log::error('Google login error: ' . $e->getMessage());
            Log::error('Google login trace: ' . $e->getTraceAsString());

            $error = urlencode('Đăng nhập Google thất bại: ' . $e->getMessage());
            return redirect("http://localhost:3000/login?error={$error}");
        }
    }

    /**
     * Check Google Login Token
     * 
     * Validate the JWT token obtained from Google login.
     * 
     * @unauthenticated
     * 
     * @queryParam token string required The JWT token to validate.
     * 
     * @response 200 {
     *   "success": true,
     *   "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *   "user": {
     *     "UserId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "admin@gauphim.com"
     *   }
     * }
     * @response 401 {
     *   "error": "Token not found"
     * }
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