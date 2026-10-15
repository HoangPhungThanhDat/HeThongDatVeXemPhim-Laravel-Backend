<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefreshToken extends Model
{
    protected $table = 'refresh_tokens';

    protected $fillable = [
        'user_id',
        'token_hash',
        'expires_at',
        'revoked',
        'user_agent',
        'ip_address',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked'    => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'UserId');
    }

    public function isValid(): bool
    {
        return !$this->revoked && $this->expires_at->isFuture();
    }
}