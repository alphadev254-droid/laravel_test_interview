<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Data\LoginData;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginAction $loginAction,
        private readonly LogoutAction $logoutAction,
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $data = LoginData::from($request->validate(LoginData::rules()));

        $result = $this->loginAction->execute($data);

        return response()->json([
            'data' => [
                'token' => $result['token'],
                'token_type' => $result['token_type'],
                'user' => new UserResource($result['user']),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->logoutAction->execute($request->user());

        return response()->json([
            'message' => 'Successfully logged out',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($request->user()),
        ]);
    }
}
