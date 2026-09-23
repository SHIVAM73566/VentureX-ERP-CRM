<?php

namespace App\Http\Controllers\Api;

use App\Models\Quotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Quotation::class);

        $query = Quotation::ofCompany()
            ->with('customer')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('quotation_number', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Quotation::class);

        $validated = $request->validate([
            'customer_id' => ['required', $this->companyExists('customers')],
            'currency_code' => 'nullable|string|size:3',
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
        ]);

        $quotation = Quotation::create([
            ...$validated,
            'quotation_number' => 'QUO-'.date('Ymd').'-'.str_pad(Quotation::max('id') + 1, 4, '0', STR_PAD_LEFT),
            'company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($quotation->load('customer'), 'Quotation created', 201);
    }

    public function show(Quotation $quotation): JsonResponse
    {
        $this->authorize('view', $quotation);

        if ($quotation->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        return $this->successResponse($quotation->load('customer', 'items', 'createdBy'));
    }

    public function update(Request $request, Quotation $quotation): JsonResponse
    {
        $this->authorize('update', $quotation);

        if ($quotation->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $validated = $request->validate([
            'customer_id' => ['sometimes', $this->companyExists('customers')],
            'currency_code' => 'nullable|string|size:3',
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string',
            'terms' => 'nullable|string',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'total' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:draft,sent,accepted,rejected,expired,converted',
        ]);

        $quotation->update($validated);

        return $this->successResponse($quotation->fresh()->load('customer', 'items'), 'Quotation updated');
    }

    public function destroy(Quotation $quotation): JsonResponse
    {
        $this->authorize('delete', $quotation);

        if ($quotation->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $quotation->delete();

        return $this->successResponse(null, 'Quotation deleted');
    }
}
