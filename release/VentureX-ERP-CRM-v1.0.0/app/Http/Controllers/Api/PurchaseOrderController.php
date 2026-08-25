<?php

namespace App\Http\Controllers\Api;

use App\Models\PurchaseOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseOrderController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = PurchaseOrder::ofCompany()
            ->with('supplier')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('po_number', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'rfq_id' => 'nullable|exists:rfqs,id',
            'order_date' => 'nullable|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder = PurchaseOrder::create([
            ...$validated,
            'po_number' => 'PO-'.date('Ymd').'-'.str_pad(PurchaseOrder::max('id') + 1, 4, '0', STR_PAD_LEFT),
            'company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($purchaseOrder->load('supplier'), 'Purchase order created', 201);
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        return $this->successResponse($purchaseOrder->load('supplier', 'rfq', 'items', 'createdBy'));
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $validated = $request->validate([
            'supplier_id' => 'sometimes|exists:suppliers,id',
            'status' => 'nullable|string|in:draft,pending,approved,ordered,partially_received,received,cancelled',
            'payment_status' => 'nullable|string|in:unpaid,partial,paid',
            'order_date' => 'nullable|date',
            'expected_date' => 'nullable|date',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'payment_terms' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $purchaseOrder->update($validated);

        return $this->successResponse($purchaseOrder->fresh()->load('supplier', 'items'), 'Purchase order updated');
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $purchaseOrder->delete();

        return $this->successResponse(null, 'Purchase order deleted');
    }
}
