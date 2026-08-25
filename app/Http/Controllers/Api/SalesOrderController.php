<?php

namespace App\Http\Controllers\Api;

use App\Models\SalesOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesOrderController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = SalesOrder::ofCompany()
            ->with('customer')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('order_number', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'quotation_id' => 'nullable|exists:quotations,id',
            'order_date' => 'nullable|date',
            'delivery_date' => 'nullable|date|after_or_equal:order_date',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $order = SalesOrder::create([
            ...$validated,
            'order_number' => 'SO-'.date('Ymd').'-'.str_pad(SalesOrder::max('id') + 1, 4, '0', STR_PAD_LEFT),
            'company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($order->load('customer'), 'Sales order created', 201);
    }

    public function show(SalesOrder $salesOrder): JsonResponse
    {
        if ($salesOrder->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        return $this->successResponse($salesOrder->load('customer', 'quotation', 'items', 'invoices', 'createdBy'));
    }

    public function update(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        if ($salesOrder->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $validated = $request->validate([
            'customer_id' => 'sometimes|exists:customers,id',
            'status' => 'nullable|string|in:draft,confirmed,processing,shipped,completed,cancelled',
            'payment_status' => 'nullable|string|in:unpaid,partial,paid',
            'order_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $salesOrder->update($validated);

        return $this->successResponse($salesOrder->fresh()->load('customer', 'items'), 'Sales order updated');
    }

    public function destroy(SalesOrder $salesOrder): JsonResponse
    {
        if ($salesOrder->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $salesOrder->delete();

        return $this->successResponse(null, 'Sales order deleted');
    }
}
