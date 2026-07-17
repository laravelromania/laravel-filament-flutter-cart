<?php

declare(strict_types=1);

namespace Modules\Api\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Api\Http\Resources\UserResource;

/**
 * Token authentication for the mobile app, backed by Sanctum personal access
 * tokens ({@see \Laravel\Sanctum\HasApiTokens} is already on the User model).
 *
 * `register` and `login` are the only public endpoints; both mint a plain-text
 * token the client stores and replays as `Authorization: Bearer <token>` on every
 * protected call. `logout` revokes the current token; `user` echoes the caller.
 */
class AuthController
{
    /** Create an account and hand back a fresh token. */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $this->tokenResponse($user, 201);
    }

    /** Exchange credentials for a token. */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['sometimes', 'string', 'max:255'],
        ]);

        $user = User::where('email', $data['email'])->first();

        // One generic error for both "no such user" and "wrong password" — never
        // leak which emails have accounts. Thrown as a ValidationException so it
        // renders in the same 422 { message, errors } shape as the rest of the API.
        if ($user === null || ! Hash::check($data['password'], (string) $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        return $this->tokenResponse($user, 200, $data['device_name'] ?? 'mobile');
    }

    /** Revoke the token that authenticated this request. */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Deconectat.']);
    }

    /** The authenticated shopper. */
    public function user(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    private function tokenResponse(User $user, int $status, string $device = 'mobile'): JsonResponse
    {
        $token = $user->createToken($device)->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user),
        ], $status);
    }
}
