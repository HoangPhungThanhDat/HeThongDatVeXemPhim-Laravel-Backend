<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterRequest;
use App\Http\Requests\StoreLoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Services\TokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
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
    protected $tokenService;

    public function __construct(AuthService $authService, TokenService $tokenService)
    {
        $this->authService  = $authService;
        $this->tokenService = $tokenService;
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
     * Authenticate a user and return a JWT access token.
     * A refresh token is set as an HttpOnly cookie for security.
     * Only users with RoleId = 1 (Admin) are allowed.
     * 
     * @unauthenticated
     * 
     * @bodyParam Email string required The email address. Example: admin@gauphim.com
     * @bodyParam Password string required The password. Example: 123456
     * 
     * @response 200 {
     *   "success": true,
     *   "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *   "expires_in": 1800,
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
     * @response 403 {
     *   "error": "Tài khoản không có quyền truy cập Admin!",
     *   "code": "NOT_ADMIN"
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

        $user = $result['user']->load('role');

        // Chỉ Admin (RoleId = 1) mới login được vào admin panel
        if ($user->RoleId != 1) {
            return response()->json([
                'error' => 'Tài khoản không có quyền truy cập Admin!',
                'code'  => 'NOT_ADMIN',
            ], 403);
        }

        if (strtolower($user->Status) !== 'active') {
            return response()->json([
                'error' => 'Tài khoản đã bị khóa!',
                'code'  => 'ACCOUNT_LOCKED',
            ], 403);
        }

        // ⭐ Tạo cặp access + refresh token qua TokenService
        $tokens = $this->tokenService->issueTokens($user);

        return response()
            ->json([
                'success'      => true,
                'access_token' => $tokens['access_token'],
                'expires_in'   => $tokens['expires_in'],
                'user'         => new UserResource($user),
            ])
            ->cookie($this->refreshCookie($tokens['refresh_token']));
    }

    /**
     * Refresh access token
     * 
     * Use the refresh token stored in HttpOnly cookie to issue a new
     * access token + rotate the refresh token (old one is revoked).
     * 
     * @unauthenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
     *   "expires_in": 1800,
     *   "user": {
     *     "UserId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "admin@gauphim.com"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Refresh token không hợp lệ hoặc đã hết hạn!",
     *   "code": "INVALID_REFRESH_TOKEN"
     * }
     */
    public function refresh(Request $request): JsonResponse
    {
        $refreshToken = $request->cookie(config('jwt.cookie_name'));

        if (!$refreshToken) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'Không tìm thấy refresh token!',
                    'code'    => 'NO_REFRESH_TOKEN',
                ], 401)
                ->cookie(Cookie::forget(config('jwt.cookie_name')));
        }

        $tokens = $this->tokenService->refresh($refreshToken);

        if (!$tokens) {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'Refresh token không hợp lệ hoặc đã hết hạn!',
                    'code'    => 'INVALID_REFRESH_TOKEN',
                ], 401)
                ->cookie(Cookie::forget(config('jwt.cookie_name')));
        }

        $user = JWTAuth::setToken($tokens['access_token'])->authenticate();
        $user->load('role');

        return response()
            ->json([
                'success'      => true,
                'access_token' => $tokens['access_token'],
                'expires_in'   => $tokens['expires_in'],
                'user'         => new UserResource($user),
            ])
            ->cookie($this->refreshCookie($tokens['refresh_token']));
    }

    /**
     * Logout from the system
     * 
     * Invalidate the current JWT token, revoke the refresh token
     * and clear the HttpOnly cookie.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Logged out"
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     */
    public function logout(Request $request): JsonResponse
    {
        $refreshToken = $request->cookie(config('jwt.cookie_name'));
        if ($refreshToken) {
            $this->tokenService->revoke($refreshToken);
        }

        try {
            JWTAuth::parseToken()->invalidate();
        } catch (\Throwable $e) {
            // bỏ qua nếu token không hợp lệ
        }

        return response()
            ->json(['success' => true, 'message' => 'Logged out'])
            ->cookie(Cookie::forget(config('jwt.cookie_name')));
    }

    /**
     * Logout from all devices
     * 
     * Revoke all refresh tokens of the current user.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Đã đăng xuất khỏi mọi thiết bị!"
     * }
     */
    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->tokenService->revokeAllForUser($user->UserId);

        try {
            JWTAuth::parseToken()->invalidate();
        } catch (\Throwable $e) {
            // bỏ qua
        }

        return response()
            ->json([
                'success' => true,
                'message' => 'Đã đăng xuất khỏi mọi thiết bị!',
            ])
            ->cookie(Cookie::forget(config('jwt.cookie_name')));
    }

    /**
     * Get current authenticated user
     * 
     * Return the profile of the currently authenticated user.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "user": {
     *     "UserId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "admin@gauphim.com",
     *     "RoleId": 1
     *   }
     * }
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');
        return response()->json([
            'success' => true,
            'user'    => new UserResource($user),
        ]);
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
     * Also sets the HttpOnly refresh token cookie.
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

            $user->load('role');

            // ⭐ Dùng TokenService để có cả refresh token
            $tokens = $this->tokenService->issueTokens($user);

            Log::info('Google login success (Admin):', [
                'user_id' => $user->UserId,
                'email' => $user->Email,
                'role_id' => $user->RoleId,
                'fullname' => $user->FullName
            ]);

            // Redirect về frontend kèm access token trong query
            // Cookie refresh_token tự động được set qua response
            return redirect("http://localhost:3000/login?token={$tokens['access_token']}")
                ->cookie($this->refreshCookie($tokens['refresh_token']));

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

    // ════════════════════════════════════════════════════════
    // 🔒 HELPER: Tạo cookie HttpOnly cho refresh token
    // ════════════════════════════════════════════════════════
    private function refreshCookie(string $refreshToken): \Symfony\Component\HttpFoundation\Cookie
    {
        $isProd = app()->environment('production');

        return cookie(
            name:     config('jwt.cookie_name'),
            value:    $refreshToken,
            minutes:  config('jwt.refresh_ttl'),
            path:     '/',
            domain:   null,                          // dev: null; prod: env('SESSION_DOMAIN')
            secure:   $isProd,                       // dev: false; prod: true
            httpOnly: true,                          // ⭐ JS không đọc được
            raw:      false,
            sameSite: $isProd ? 'none' : 'lax',
        );
    }
}