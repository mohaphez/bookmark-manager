<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\Api\V1\User\UserResource;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponse;

    /**
     * Handle user login request
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = userService()->authenticate(
            $credentials['email'],
            $credentials['password']
        );

        if (! $user) {
            return $this->error(
                message: __('The provided credentials are incorrect.'),
                code: 401
            );
        }

        $token = userService()->createToken($user, 'auth_token');

        return $this->success(
            data: [
                'user' => new UserResource($user),
                'access_token' => $token,
                'token_type' => 'Bearer',
            ],
            message: __('User logged in successfully')
        );
    }

    /**
     * Handle user logout request
     */
    public function logout(): JsonResponse
    {
        $user = userService()->findById(Auth::id());

        if ($user) {
            userService()->revokeTokens($user);
        }

        return $this->success(
            data: null,
            message: __('User logged out successfully')
        );
    }
}
