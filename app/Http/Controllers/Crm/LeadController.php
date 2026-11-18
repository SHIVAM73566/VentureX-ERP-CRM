<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\LeadRequest;
use App\Models\Country;
use App\Models\Lead;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\NumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeadController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Lead::class);

        $leads = Lead::ofCompany()
            ->with('assignedTo', 'country')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('contact_name', 'like', "%{$search}%")
                        ->orWhere('company_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->string('source')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('crm.leads.index', [
            'leads' => $leads,
            'statuses' => Lead::STATUSES,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Lead::class);

        return view('crm.leads.form', $this->formData(new Lead));
    }

    public function store(LeadRequest $request): RedirectResponse
    {
        $this->authorize('create', Lead::class);

        try {
            $lead = DB::transaction(function () use ($request) {
                return Lead::create([
                    ...$request->validated(),
                    'company_id' => CompanyContext::id(),
                    'lead_number' => $this->nextNumber(),
                    'created_by' => auth()->id(),
                ]);
            });

            AuditLogger::created($lead);

            return redirect()->route('leads.show', $lead)->with('success', 'Lead created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create lead: '.$e->getMessage());
        }
    }

    public function show(Lead $lead): View
    {
        $this->authorize('view', $lead);

        if ((int) $lead->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $lead->load('assignedTo', 'country', 'activities', 'opportunity');

        return view('crm.leads.show', ['lead' => $lead]);
    }

    public function edit(Lead $lead): View
    {
        $this->authorize('update', $lead);

        if ((int) $lead->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        return view('crm.leads.form', $this->formData($lead));
    }

    public function update(LeadRequest $request, Lead $lead): RedirectResponse
    {
        $this->authorize('update', $lead);

        if ((int) $lead->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $lead) {
                $lead->update($request->validated());
            });
            AuditLogger::updated($lead);

            return redirect()->route('leads.show', $lead)->with('success', 'Lead updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update lead: '.$e->getMessage());
        }
    }

    public function destroy(Lead $lead): RedirectResponse
    {
        $this->authorize('delete', $lead);

        if ((int) $lead->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $lead->delete();
        AuditLogger::deleted($lead);

        return redirect()->route('leads.index')->with('success', 'Lead deleted.');
    }

    protected function nextNumber(): string
    {
        // Database-backed sequence with lockForUpdate(); no cache lock or
        // cross-request counter that can drift under concurrent staff load.
        return NumberGenerator::next('LEAD', 'leads');
    }

    protected function formData(Lead $lead): array
    {
        return [
            'lead' => $lead,
            'countries' => Country::orderBy('name')->get(),
            'users' => User::ofCompany()->orderBy('name')->get(),
            'statuses' => Lead::STATUSES,
            'sources' => ['Website', 'Referral', 'Cold Call', 'Email', 'WhatsApp', 'Trade Show', 'LinkedIn', 'Other'],
        ];
    }
}
