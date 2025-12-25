<?php

namespace App\Services;

use App\Repositories\AuthRepository;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    protected $users;

    public function __construct(AuthRepository $users)
    {
        $this->users = $users;
    }

    public function register(array $data)
    {
        $data['PasswordHash'] = Hash::make($data['Password']);
        unset($data['Password']); // không lưu plain text

        $user = $this->users->create($data);
        $token = JWTAuth::fromUser($user);

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function login(string $emailOrName, string $password)
    {
        $user = $this->users->findByEmailOrName($emailOrName);

        if (!$user || !Hash::check($password, $user->PasswordHash)) {
            return null;
        }

        $token = JWTAuth::fromUser($user);

        return [
            'user'  => $user,
            'token' => $token,
        ];
    }

    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
    }
}
