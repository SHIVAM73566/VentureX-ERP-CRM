<?php

namespace App\Http\Controllers\Api;

use App\Models\Product;
use App\Services\DashboardCacheService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $query = Product::ofCompany()
            ->with('unit', 'supplier', 'taxRate')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->latest();

        $products = $query->paginate($this->perPage($request));

        // One grouped ledger query for the whole page instead of one per row.
        $balances = StockService::balances($this->companyId());
        foreach ($products->items() as $product) {
            $product->setAttribute('available_stock', (float) ($balances[$product->id] ?? 0));
            $product->setAttribute('is_low_stock', $product->reorder_level !== null
                && (float) ($balances[$product->id] ?? 0) <= (float) $product->reorder_level);
        }

        return $this->paginatedResponse($products);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'sku' => 'required|string|max:50|unique:products,sku,NULL,company_id,'.$this->companyId(),
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'reorder_level' => 'nullable|numeric|min:0',
            'supplier_id' => ['nullable', $this->companyExists('suppliers')],
            'status' => 'nullable|string|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        try {
            $product = DB::transaction(fn () => Product::create([
                ...$validated,
                'company_id' => auth()->user()->company_id,
                'created_by' => auth()->id(),
            ]));
        } catch (\Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to create product.', 500);
        }

        DashboardCacheService::forget((int) $product->company_id);

        return $this->successResponse($product->load('unit', 'supplier', 'taxRate'), 'Product created', 201);
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        if ($product->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        return $this->successResponse($product->load('unit', 'supplier', 'taxRate')->toArray() + [
            'available_stock' => $product->availableStock(),
            'is_low_stock' => $product->isLowStock(),
        ]);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);

        if ($product->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'description' => 'nullable|string',
            'unit_id' => 'nullable|exists:units,id',
            'purchase_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
            'tax_rate_id' => 'nullable|exists:tax_rates,id',
            'reorder_level' => 'nullable|numeric|min:0',
            'supplier_id' => ['nullable', $this->companyExists('suppliers')],
            'status' => 'nullable|string|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::transaction(fn () => $product->update($validated));
        } catch (\Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to update product.', 500);
        }

        DashboardCacheService::forget((int) $product->company_id);

        return $this->successResponse($product->fresh()->load('unit', 'supplier', 'taxRate'), 'Product updated');
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);

        if ($product->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        try {
            DB::transaction(fn () => $product->delete());
        } catch (\Throwable $e) {
            report($e);

            return $this->errorResponse('Failed to delete product.', 500);
        }

        DashboardCacheService::forget((int) $product->company_id);

        return $this->successResponse(null, 'Product deleted');
    }
}
