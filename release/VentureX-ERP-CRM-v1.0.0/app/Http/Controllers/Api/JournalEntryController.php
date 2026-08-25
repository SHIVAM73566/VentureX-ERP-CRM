<?php

namespace App\Http\Controllers\Api;

use App\Models\JournalEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JournalEntryController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = JournalEntry::ofCompany()
            ->with('lines')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('entry_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest();

        return $this->paginatedResponse($query->paginate($request->integer('per_page', 15)));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'description' => 'nullable|string|max:500',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|exists:chart_of_accounts,id',
            'lines.*.debit' => 'nullable|numeric|min:0',
            'lines.*.credit' => 'nullable|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        $totalDebit = collect($validated['lines'])->sum('debit');
        $totalCredit = collect($validated['lines'])->sum('credit');

        if (abs($totalDebit - $totalCredit) >= 0.0001) {
            return $this->errorResponse('Journal entry must be balanced (debit = credit)', 422);
        }

        $entry = JournalEntry::create([
            'entry_number' => 'JE-'.date('Ymd').'-'.str_pad(JournalEntry::max('id') + 1, 4, '0', STR_PAD_LEFT),
            'date' => $validated['date'],
            'description' => $validated['description'] ?? null,
            'company_id' => auth()->user()->company_id,
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['lines'] as $line) {
            $entry->lines()->create($line);
        }

        return $this->successResponse($entry->load('lines'), 'Journal entry created', 201);
    }

    public function show(JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        return $this->successResponse($journalEntry->load('lines.account', 'createdBy'));
    }

    public function update(Request $request, JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        if ($journalEntry->status === 'posted') {
            return $this->errorResponse('Cannot update a posted journal entry', 422);
        }

        $validated = $request->validate([
            'date' => 'sometimes|date',
            'description' => 'nullable|string|max:500',
            'status' => 'nullable|string|in:draft,posted',
        ]);

        $journalEntry->update($validated);

        return $this->successResponse($journalEntry->fresh()->load('lines'), 'Journal entry updated');
    }

    public function destroy(JournalEntry $journalEntry): JsonResponse
    {
        if ($journalEntry->company_id !== $this->companyId()) {
            return $this->errorResponse('Not found', 404);
        }

        if ($journalEntry->status === 'posted') {
            return $this->errorResponse('Cannot delete a posted journal entry', 422);
        }

        $journalEntry->lines()->delete();
        $journalEntry->delete();

        return $this->successResponse(null, 'Journal entry deleted');
    }
}
