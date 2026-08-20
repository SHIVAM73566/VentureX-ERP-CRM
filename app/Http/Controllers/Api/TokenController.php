<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TokenController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $tokens = $request->user()->tokens->map(fn ($token) => [
            'id' => $token->id,
            'name' => $token->name,
            'abilities' => $token->abilities,
            'last_used_at' => $token->last_used_at?->toISOString(),
            'created_at' => $token->created_at?->toISOString(),
        ]);

        return $this->successResponse($tokens);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'abilities' => 'nullable|array',
            'abilities.*' => 'string',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $token = $request->user()->createToken(
            $validated['name'],
            $validated['abilities'] ?? ['*'],
            $validated['expires_at'] ?? null
        );

        return $this->successResponse([
            'id' => $token->accessToken->id,
            'name' => $token->accessToken->name,
            'token' => $token->plainTextToken,
            'abilities' => $token->accessToken->abilities,
            'expires_at' => $token->accessToken->expires_at?->toISOString(),
            'created_at' => $token->accessToken->created_at?->toISOString(),
        ], 'Token created', 201);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $token = $request->user()->tokens()->find($id);

        if (! $token) {
            return $this->errorResponse('Token not found', 404);
        }

        $token->delete();

        return $this->successResponse(null, 'Token revoked');
    }
}
