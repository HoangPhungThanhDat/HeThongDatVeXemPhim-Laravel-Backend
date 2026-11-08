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
use App\Models\Auditlog;
use Laravel\Socialite\Facades\Socialite;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected $authService;
    protected $tokenService;

    public function __construct(AuthService $authService, TokenService $tokenService)
    {
        $this->authService  = $authService;
        $this->tokenService = $tokenService;
    }

    public function register(StoreRegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'user' => new UserResource($result['user']->load('role')),
        ], 201);
    }

    /**
     * ⭐ LOGIN CHUNG — DÀNH CHO CLIENT (mọi role)
     * Chỉ kiểm tra tài khoản active, KHÔNG check role.
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

        // Chỉ kiểm tra trạng thái active
        if (strtolower($user->Status) !== 'active') {
            return response()->json([
                'error' => 'Tài khoản đã bị khóa!',
                'code'  => 'ACCOUNT_LOCKED',
            ], 403);
        }

        // Ghi log đăng nhập
        Auditlog::create([
            'UserId'      => $user->UserId,
            'Action'      => 'Login',
            'Description' => "{$user->Email} đã đăng nhập vào hệ thống",
            'IpAddress'   => $request->ip(),
            'DeviceInfo'  => $request->userAgent(),
            'CreatedAt'   => Carbon::now(),
        ]);

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
     * ⭐ LOGIN ADMIN — DÀNH RIÊNG CHO ADMIN PANEL
     * Chỉ Admin (RoleId = 1) mới login được.
     */
    public function adminLogin(StoreLoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->Email,
            $request->Password
        );

        if (!$result) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        $user = $result['user']->load('role');

        // Chỉ Admin mới login được vào admin panel
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

        // Ghi log đăng nhập admin
        Auditlog::create([
            'UserId'      => $user->UserId,
            'Action'      => 'Login',
            'Description' => "{$user->Email} đã đăng nhập vào Admin Panel",
            'IpAddress'   => $request->ip(),
            'DeviceInfo'  => $request->userAgent(),
            'CreatedAt'   => Carbon::now(),
        ]);

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

        // Nếu tài khoản bị khóa giữa chừng → từ chối refresh
        if (strtolower($user->Status) !== 'active') {
            return response()
                ->json([
                    'success' => false,
                    'message' => 'Tài khoản đã bị khóa!',
                    'code'    => 'ACCOUNT_LOCKED',
                ], 403)
                ->cookie(Cookie::forget(config('jwt.cookie_name')));
        }

        return response()
            ->json([
                'success'      => true,
                'access_token' => $tokens['access_token'],
                'expires_in'   => $tokens['expires_in'],
                'user'         => new UserResource($user),
            ])
            ->cookie($this->refreshCookie($tokens['refresh_token']));
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        $refreshToken = $request->cookie(config('jwt.cookie_name'));

        if ($refreshToken) {
            $this->tokenService->revoke($refreshToken);
        }

        try {
            JWTAuth::parseToken()->invalidate();
        } catch (\Throwable $e) {
            // bỏ qua nếu token không hợp lệ
        }

        if ($user) {
            Auditlog::create([
                'UserId'      => $user->UserId,
                'Action'      => 'Logout',
                'Description' => "{$user->Email} đã đăng xuất khỏi hệ thống",
                'IpAddress'   => $request->ip(),
                'DeviceInfo'  => $request->userAgent(),
                'CreatedAt'   => Carbon::now(),
            ]);
        }

        return response()
            ->json(['success' => true, 'message' => 'Logged out'])
            ->cookie(Cookie::forget(config('jwt.cookie_name')));
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->tokenService->revokeAllForUser($user->UserId);

        try {
            JWTAuth::parseToken()->invalidate();
        } catch (\Throwable $e) {
            // bỏ qua
        }

        Auditlog::create([
            'UserId'      => $user->UserId,
            'Action'      => 'Logout',
            'Description' => "{$user->Email} đã đăng xuất khỏi mọi thiết bị",
            'IpAddress'   => $request->ip(),
            'DeviceInfo'  => $request->userAgent(),
            'CreatedAt'   => Carbon::now(),
        ]);

        return response()
            ->json([
                'success' => true,
                'message' => 'Đã đăng xuất khỏi mọi thiết bị!',
            ])
            ->cookie(Cookie::forget(config('jwt.cookie_name')));
    }

    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');
        return response()->json([
            'success' => true,
            'user'    => new UserResource($user),
        ]);
    }

    // ============================================================
    // GOOGLE OAUTH
    // ============================================================

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    /**
     * ⭐ Google callback — hỗ trợ cả admin (3000) và client (3005)
     * Dùng query param `?app=admin|client` để biết redirect về đâu.
     */
    public function handleGoogleCallback(Request $request)
    {
        $app = $request->query('app', 'admin');

        $frontendUrl = $app === 'client'
            ? env('FRONTEND_CLIENT_URL', 'http://localhost:3005')
            : env('FRONTEND_ADMIN_URL', 'http://localhost:3000');

        $signInPath = $app === 'client' ? '/dang-nhap' : '/auth/sign-in';

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('Email', $googleUser->getEmail())->first();

            if (!$user) {
                $error = urlencode('Email chưa được đăng ký. Vui lòng liên hệ quản trị viên để được cấp quyền.');
                return redirect("{$frontendUrl}{$signInPath}?error={$error}");
            }

            // ⭐ Chỉ admin app mới cần check role Admin
            if ($app === 'admin' && $user->RoleId != 1) {
                $error = urlencode('Tài khoản không có quyền truy cập Admin. Vui lòng liên hệ quản trị viên.');
                return redirect("{$frontendUrl}{$signInPath}?error={$error}");
            }

            if (strtolower($user->Status) !== 'active') {
                $error = urlencode('Tài khoản đã bị khóa. Vui lòng liên hệ quản trị viên.');
                return redirect("{$frontendUrl}{$signInPath}?error={$error}");
            }

            $user->update(['UpdatedAt' => now()]);
            $user->load('role');

            Auditlog::create([
                'UserId'      => $user->UserId,
                'Action'      => 'Login',
                'Description' => "{$user->Email} đã đăng nhập bằng Google ({$app})",
                'IpAddress'   => $request->ip(),
                'DeviceInfo'  => $request->userAgent(),
                'CreatedAt'   => Carbon::now(),
            ]);

            $tokens = $this->tokenService->issueTokens($user);

            return redirect("{$frontendUrl}{$signInPath}?token={$tokens['access_token']}")
                ->cookie($this->refreshCookie($tokens['refresh_token']));

        } catch (\Exception $e) {
            Log::error('Google login error: ' . $e->getMessage());

            $error = urlencode('Đăng nhập Google thất bại: ' . $e->getMessage());
            return redirect("{$frontendUrl}{$signInPath}?error={$error}");
        }
    }

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
                'token'   => $token,
                'user'    => new UserResource($user->load('role')),
            ]);
        } catch (JWTException $e) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
    }

    // ============================================================
    // COOKIE HELPER
    // ============================================================

    private function refreshCookie(string $refreshToken): \Symfony\Component\HttpFoundation\Cookie
    {
        $isProd = app()->environment('production');

        return cookie(
            name:     config('jwt.cookie_name'),
            value:    $refreshToken,
            minutes:  config('jwt.refresh_ttl'),
            path:     '/',
            domain:   null,
            secure:   $isProd,
            httpOnly: true,
            raw:      false,
            sameSite: 'lax',
        );
    }
}