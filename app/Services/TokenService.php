<?php

namespace App\Services;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Facades\JWTAuth;

class TokenService
{
    /**
     * Tạo cặp access + refresh token cho user.
     */
    public function issueTokens(User $user): array
    {
        // Access token
        $accessToken = JWTAuth::fromUser($user);

        // Refresh token
        $refreshToken = Str::random(64);
        $refreshHash  = hash('sha256', $refreshToken);

        RefreshToken::create([
            'user_id'    => $user->UserId,
            'token_hash' => $refreshHash,
            'expires_at' => now()->addMinutes(config('jwt.refresh_ttl')),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expires_in'    => config('jwt.ttl') * 60,
        ];
    }

    /**
     * Refresh token: verify → revoke cũ → cấp cặp mới.
     */
    public function refresh(string $refreshToken): ?array
    {
        $hash  = hash('sha256', $refreshToken);
        $model = RefreshToken::where('token_hash', $hash)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$model) {
            return null;
        }

        $user = User::with('role')->find($model->user_id);
        if (!$user) {
            return null;
        }

        // Revoke token cũ
        $model->update(['revoked' => true]);

        return $this->issueTokens($user);
    }

    /**
     * Revoke 1 refresh token cụ thể.
     */
    public function revoke(string $refreshToken): void
    {
        $hash = hash('sha256', $refreshToken);
        RefreshToken::where('token_hash', $hash)
            ->update(['revoked' => true]);
    }

    /**
     * Revoke tất cả refresh token của user.
     */
    public function revokeAllForUser(int $userId): void
    {
        RefreshToken::where('user_id', $userId)
            ->where('revoked', false)
            ->update(['revoked' => true]);
    }

    /**
     * Dọn dẹp token hết hạn / đã revoke.
     */
    public function cleanupExpired(): int
    {
        return RefreshToken::where(function ($q) {
                $q->where('expires_at', '<', now()->subDays(7))
                  ->orWhere(function ($q2) {
                      $q2->where('revoked', true)
                         ->where('updated_at', '<', now()->subDays(7));
                  });
            })
            ->delete();
    }
}