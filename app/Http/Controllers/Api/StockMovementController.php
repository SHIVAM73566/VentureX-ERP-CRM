<?php

namespace App\Http\Controllers\Api;

use App\Models\StockMovement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StockMovementController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = StockMovement::ofCompany()
            ->with('product', 'warehouse')
            ->when($request->filled('product_id'), fn ($q) => $q->where('product_id', $request->integer('product_id')))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'type' => 'required|string|in:in,out,transfer,adjustment',
            'quantity' => 'required|numeric|min:0.01',
            'unit_cost' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
        ]);

        $stockMovement = StockMovement::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($stockMovement->load('product', 'warehouse'), 'Stock movement recorded', 201);
    }

    public function show(StockMovement $stockMovement): JsonResponse
    {
        if ($stockMovement->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        return $this->successResponse($stockMovement->load('product', 'warehouse', 'createdBy'));
    }

    public function destroy(StockMovement $stockMovement): JsonResponse
    {
        if ($stockMovement->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $stockMovement->delete();

        return $this->successResponse(null, 'Stock movement deleted');
    }
}
