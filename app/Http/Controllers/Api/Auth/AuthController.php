<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(Request $request): JsonResponse
    {
        return $this->authService->login($request);
    }

    public function tokenLogin(Request $request): JsonResponse
    {
        return $this->authService->tokenLogin($request);
    }

    public function logout(Request $request): JsonResponse
    {
        return $this->authService->logout($request);
    }

    public function tokenLogout(Request $request): JsonResponse
    {
        return $this->authService->tokenLogout($request);
    }
}
