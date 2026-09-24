<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TokenController extends ApiController
{
    /**
     * Abilities an API token may carry. Each maps to module read/write scope.
     */
    public const ALLOWED_ABILITIES = [
        'customers.read', 'customers.write',
        'contacts.read', 'contacts.write',
        'leads.read', 'leads.write',
        'opportunities.read', 'opportunities.write',
        'quotations.read', 'quotations.write',
        'sales.read', 'sales.write',
        'invoices.read', 'invoices.write',
        'payments.read', 'payments.write',
        'products.read', 'products.write',
        'warehouses.read', 'warehouses.write',
        'stock.read', 'stock.write',
        'suppliers.read', 'suppliers.write',
        'purchase.read', 'purchase.write',
        'accounts.read', 'accounts.write',
        'journal.read', 'journal.write',
        'ai.read', 'ai.write',
        'tickets.read', 'tickets.write',
    ];

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
            'abilities.*' => ['string', Rule::in(self::ALLOWED_ABILITIES)],
            'expires_at' => 'nullable|date|after:now',
        ]);

        $abilities = $validated['abilities'] ?? ['customers.read', 'products.read', 'invoices.read'];

        $token = $request->user()->createToken(
            $validated['name'],
            $abilities,
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
