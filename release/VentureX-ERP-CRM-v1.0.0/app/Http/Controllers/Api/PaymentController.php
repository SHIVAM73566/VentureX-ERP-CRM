<?php

namespace App\Http\Controllers\Api;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Payment::ofCompany()
            ->with('customer', 'invoice')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('payment_number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'payment_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|in:cash,bank,cheque,card,upi,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment = Payment::create([
            ...$validated,
            'payment_number' => 'PAY-'.date('Ymd').'-'.str_pad(Payment::max('id') + 1, 4, '0', STR_PAD_LEFT),
            'company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($payment->load('customer', 'invoice'), 'Payment created', 201);
    }

    public function show(Payment $payment): JsonResponse
    {
        if ($payment->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        return $this->successResponse($payment->load('customer', 'invoice', 'createdBy'));
    }

    public function update(Request $request, Payment $payment): JsonResponse
    {
        if ($payment->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $validated = $request->validate([
            'status' => 'nullable|string|in:pending,completed,failed,refunded',
            'amount' => 'sometimes|numeric|min:0.01',
            'method' => 'sometimes|string|in:cash,bank,cheque,card,upi,other',
            'reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $payment->update($validated);

        return $this->successResponse($payment->fresh()->load('customer', 'invoice'), 'Payment updated');
    }

    public function destroy(Payment $payment): JsonResponse
    {
        if ($payment->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $payment->delete();

        return $this->successResponse(null, 'Payment deleted');
    }
}
