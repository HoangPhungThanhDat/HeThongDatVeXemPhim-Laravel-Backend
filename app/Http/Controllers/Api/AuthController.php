<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegisterRequest;
use App\Http\Requests\StoreLoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

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
}
