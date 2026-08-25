<?php

namespace App\Http\Controllers\Api;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Customer::ofCompany()
            ->withCount('contacts', 'opportunities')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|integer',
            'currency_code' => 'nullable|string|size:3',
            'status' => 'nullable|string|in:active,inactive',
            'notes' => 'nullable|string',
            'branch_id' => 'nullable|integer',
        ]);

        $customer = Customer::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($customer->load('contacts', 'opportunities'), 'Customer created', 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        if ($customer->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $customer->load('contacts', 'opportunities', 'country', 'branch');

        return $this->successResponse($customer);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        if ($customer->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:50',
            'industry' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'address_line1' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country_id' => 'nullable|integer',
            'currency_code' => 'nullable|string|size:3',
            'status' => 'nullable|string|in:active,inactive',
            'notes' => 'nullable|string',
            'branch_id' => 'nullable|integer',
        ]);

        $customer->update($validated);

        return $this->successResponse($customer->fresh()->load('contacts', 'opportunities'), 'Customer updated');
    }

    public function destroy(Customer $customer): JsonResponse
    {
        if ($customer->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $customer->delete();

        return $this->successResponse(null, 'Customer deleted');
    }
}
