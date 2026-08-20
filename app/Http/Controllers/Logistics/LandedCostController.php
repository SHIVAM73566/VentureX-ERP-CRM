<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\LandedCost;
use App\Models\Shipment;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LandedCostController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LandedCost::class);

        $costs = LandedCost::ofCompany()
            ->with('shipment', 'container')
            ->when($request->filled('cost_type'), fn ($q) => $q->where('cost_type', $request->string('cost_type')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => (float) LandedCost::ofCompany()->sum('amount'),
            'freight' => (float) LandedCost::ofCompany()->where('cost_type', 'freight')->sum('amount'),
            'duties' => (float) LandedCost::ofCompany()->where('cost_type', 'customs_duty')->sum('amount'),
            'count' => LandedCost::ofCompany()->count(),
        ];

        return view('logistics.landed-costs.index', compact('costs', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create', LandedCost::class);

        return view('logistics.landed-costs.form', [
            'cost' => new LandedCost,
            'shipments' => Shipment::ofCompany()->orderByDesc('id')->get(),
            'containers' => Container::ofCompany()->orderBy('container_number')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LandedCost::class);

        try {
            $cost = DB::transaction(function () use ($request) {
                return LandedCost::create([
                    'company_id' => CompanyContext::id(),
                    'created_by' => auth()->id(),
                    ...$request->validate($this->rules()),
                ]);
            });

            AuditLogger::created($cost);

            return redirect()->route('logistics.landed-costs.index')->with('success', 'Landed cost recorded.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create landed cost: '.$e->getMessage());
        }
    }

    public function edit(LandedCost $cost): View
    {
        $this->authorize('update', $cost);

        if ((int) $cost->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        return view('logistics.landed-costs.form', [
            'cost' => $cost,
            'shipments' => Shipment::ofCompany()->orderByDesc('id')->get(),
            'containers' => Container::ofCompany()->orderBy('container_number')->get(),
        ]);
    }

    public function update(Request $request, LandedCost $cost): RedirectResponse
    {
        $this->authorize('update', $cost);

        if ((int) $cost->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $cost) {
                $cost->update($request->validate($this->rules()));
            });
            AuditLogger::updated($cost);

            return redirect()->route('logistics.landed-costs.index')->with('success', 'Landed cost updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update landed cost: '.$e->getMessage());
        }
    }

    public function destroy(LandedCost $cost): RedirectResponse
    {
        $this->authorize('delete', $cost);

        if ((int) $cost->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $cost->delete();
        AuditLogger::deleted($cost);

        return redirect()->route('logistics.landed-costs.index')->with('success', 'Landed cost deleted.');
    }

    protected function rules(): array
    {
        return [
            'shipment_id' => ['nullable', CompanyContext::existsInCompany('shipments')],
            'container_id' => ['nullable', CompanyContext::existsInCompany('containers')],
            'cost_type' => ['required', 'in:freight,customs_duty,insurance,handling,storage,inspection,other'],
            'description' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'max:8'],
            'paid_to' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
