<?php

namespace App\Http\Controllers\Api;

use App\Models\Opportunity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = Opportunity::query()
            ->with('customer', 'lead', 'assignedTo')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('stage'), fn ($q) => $q->where('stage', $request->string('stage')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'lead_id' => 'nullable|exists:leads,id',
            'expected_value' => 'nullable|numeric|min:0',
            'currency_code' => 'nullable|string|size:3',
            'stage' => 'nullable|string|in:qualification,needs_analysis,proposal,negotiation,won,lost',
            'probability' => 'nullable|numeric|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'branch_id' => 'nullable|integer',
        ]);

        $opportunity = Opportunity::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        return $this->successResponse($opportunity->load('customer', 'lead', 'assignedTo'), 'Opportunity created', 201);
    }

    public function show(Opportunity $opportunity): JsonResponse
    {
        return $this->successResponse($opportunity->load('customer', 'lead', 'assignedTo', 'activities'));
    }

    public function update(Request $request, Opportunity $opportunity): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'expected_value' => 'nullable|numeric|min:0',
            'currency_code' => 'nullable|string|size:3',
            'stage' => 'nullable|string|in:qualification,needs_analysis,proposal,negotiation,won,lost',
            'probability' => 'nullable|numeric|min:0|max:100',
            'expected_close_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'source' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        $opportunity->update($validated);

        return $this->successResponse($opportunity->fresh()->load('customer', 'lead', 'assignedTo'), 'Opportunity updated');
    }

    public function destroy(Opportunity $opportunity): JsonResponse
    {
        $opportunity->delete();

        return $this->successResponse(null, 'Opportunity deleted');
    }
}
