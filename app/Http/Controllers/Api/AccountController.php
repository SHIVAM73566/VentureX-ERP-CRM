<?php

namespace App\Http\Controllers\Api;

use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Account::query()
            ->with('parent')
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('code');

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 50)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:chart_of_accounts,code',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $account = Account::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
        ]);

        return $this->successResponse($account->load('parent'), 'Account created', 201);
    }

    public function show(Account $account): JsonResponse
    {
        return $this->successResponse($account->load('parent', 'children')->toArray() + [
            'balance' => $account->balance(),
        ]);
    }

    public function update(Request $request, Account $account): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|exists:chart_of_accounts,id',
            'is_active' => 'nullable|boolean',
            'description' => 'nullable|string',
        ]);

        $account->update($validated);

        return $this->successResponse($account->fresh()->load('parent', 'children'), 'Account updated');
    }

    public function destroy(Account $account): JsonResponse
    {
        if ($account->lines()->exists()) {
            return $this->errorResponse('Cannot delete account with journal entries', 422);
        }

        $account->delete();

        return $this->successResponse(null, 'Account deleted');
    }
}
