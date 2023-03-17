<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(public readonly AuthService $authService)
    {
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function attempt(Request $request): JsonResponse
    {
        $username = (string)$request->get('username');
        $login = $this->authService->login($username);

        return response()->json($login->toArray());
    }
}
