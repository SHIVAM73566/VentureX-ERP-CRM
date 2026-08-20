<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpportunityRequest;
use App\Models\Customer;
use App\Models\Opportunity;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OpportunityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Opportunity::class);

        $opportunities = Opportunity::ofCompany()
            ->with('customer', 'assignedTo')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('stage'), fn ($q) => $q->where('stage', $request->string('stage')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('crm.opportunities.index', [
            'opportunities' => $opportunities,
            'stages' => Opportunity::STAGES,
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Opportunity::class);

        return view('crm.opportunities.form', $this->formData(new Opportunity));
    }

    public function store(OpportunityRequest $request): RedirectResponse
    {
        $this->authorize('create', Opportunity::class);

        try {
            $opportunity = DB::transaction(function () use ($request) {
                return Opportunity::create([
                    ...$request->validated(),
                    'company_id' => CompanyContext::id(),
                    'created_by' => auth()->id(),
                ]);
            });

            AuditLogger::created($opportunity);

            return redirect()->route('opportunities.show', $opportunity)->with('success', 'Opportunity created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create opportunity: '.$e->getMessage());
        }
    }

    public function show(Opportunity $opportunity): View
    {
        $this->authorize('view', $opportunity);

        if ((int) $opportunity->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $opportunity->load('customer', 'assignedTo', 'activities');

        return view('crm.opportunities.show', ['opportunity' => $opportunity]);
    }

    public function edit(Opportunity $opportunity): View
    {
        $this->authorize('update', $opportunity);

        if ((int) $opportunity->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        return view('crm.opportunities.form', $this->formData($opportunity));
    }

    public function update(OpportunityRequest $request, Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('update', $opportunity);

        if ((int) $opportunity->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $opportunity) {
                $opportunity->update($request->validated());
            });
            AuditLogger::updated($opportunity);

            return redirect()->route('opportunities.show', $opportunity)->with('success', 'Opportunity updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update opportunity: '.$e->getMessage());
        }
    }

    public function destroy(Opportunity $opportunity): RedirectResponse
    {
        $this->authorize('delete', $opportunity);

        if ((int) $opportunity->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $opportunity->delete();
        AuditLogger::deleted($opportunity);

        return redirect()->route('opportunities.index')->with('success', 'Opportunity deleted.');
    }

    protected function formData(Opportunity $opportunity): array
    {
        return [
            'opportunity' => $opportunity,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'users' => User::ofCompany()->orderBy('name')->get(),
            'stages' => Opportunity::STAGES,
        ];
    }
}
