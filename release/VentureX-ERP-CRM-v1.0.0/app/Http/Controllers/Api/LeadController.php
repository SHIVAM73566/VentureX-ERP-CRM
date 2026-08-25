<?php

namespace App\Http\Controllers\Api;

use App\Models\Lead;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Lead::ofCompany()
            ->with('assignedTo', 'country')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'product_interest' => 'nullable|string|max:255',
            'estimated_value' => 'nullable|numeric|min:0',
            'currency_code' => 'nullable|string|size:3',
            'status' => 'nullable|string|in:new,contacted,qualified,proposal,won,lost',
            'score' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'website' => 'nullable|url|max:255',
            'next_follow_up' => 'nullable|date',
            'notes' => 'nullable|string',
            'country_id' => 'nullable|integer',
            'branch_id' => 'nullable|integer',
        ]);

        $lead = Lead::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($lead->load('assignedTo', 'country'), 'Lead created', 201);
    }

    public function show(Lead $lead): JsonResponse
    {
        if ($lead->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        return $this->successResponse($lead->load('assignedTo', 'country', 'opportunity'));
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        if ($lead->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $validated = $request->validate([
            'company_name' => 'sometimes|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'source' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'product_interest' => 'nullable|string|max:255',
            'estimated_value' => 'nullable|numeric|min:0',
            'currency_code' => 'nullable|string|size:3',
            'status' => 'nullable|string|in:new,contacted,qualified,proposal,won,lost',
            'score' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'website' => 'nullable|url|max:255',
            'next_follow_up' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return $this->successResponse($lead->fresh()->load('assignedTo', 'country'), 'Lead updated');
    }

    public function destroy(Lead $lead): JsonResponse
    {
        if ($lead->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        $lead->delete();

        return $this->successResponse(null, 'Lead deleted');
    }
}
