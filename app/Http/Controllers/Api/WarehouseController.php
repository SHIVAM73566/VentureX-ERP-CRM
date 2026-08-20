<?php

namespace App\Http\Controllers\Api;

use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Warehouse::query()
            ->with('manager')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:warehouses,code',
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $warehouse = Warehouse::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
        ]);

        return $this->successResponse($warehouse->load('manager'), 'Warehouse created', 201);
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        return $this->successResponse($warehouse->load('manager'));
    }

    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'manager_id' => 'nullable|exists:users,id',
            'capacity' => 'nullable|numeric|min:0',
            'is_default' => 'nullable|boolean',
            'status' => 'nullable|string|in:active,inactive',
        ]);

        $warehouse->update($validated);

        return $this->successResponse($warehouse->fresh()->load('manager'), 'Warehouse updated');
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $warehouse->delete();

        return $this->successResponse(null, 'Warehouse deleted');
    }
}
