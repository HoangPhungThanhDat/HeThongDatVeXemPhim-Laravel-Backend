<?php

namespace App\Repositories;

use App\Models\User;

class AuthRepository
{
    public function create(array $data): User
    {
        return User::create($data);
    }

    public function findByEmailOrName(string $value): ?User
    {
        return User::where('Email', $value)
            ->orWhere('FullName', $value)
            ->first();
    }
}
