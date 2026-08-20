<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Supplier;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Supplier::class);

        $suppliers = Supplier::ofCompany()
            ->withCount('offers')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('tax_id', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('procurement.suppliers.index', ['suppliers' => $suppliers]);
    }

    public function create(): View
    {
        $this->authorize('create', Supplier::class);

        return view('procurement.suppliers.form', [
            'supplier' => new Supplier,
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Supplier::class);

        $data = $this->validated($request);

        try {
            $supplier = DB::transaction(function () use ($data) {
                return Supplier::create([
                    ...$data,
                    'company_id' => CompanyContext::id(),
                    'created_by' => auth()->id(),
                ]);
            });

            AuditLogger::created($supplier);

            return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create supplier: '.$e->getMessage());
        }
    }

    public function show(Supplier $supplier): View
    {
        $this->authorize('view', $supplier);

        if ((int) $supplier->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $supplier->load('offers', 'country');

        return view('procurement.suppliers.show', [
            'supplier' => $supplier,
            'offers' => $supplier->offers()->latest()->paginate(10),
        ]);
    }

    public function edit(Supplier $supplier): View
    {
        $this->authorize('update', $supplier);

        if ((int) $supplier->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        return view('procurement.suppliers.form', [
            'supplier' => $supplier,
            'countries' => Country::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $this->authorize('update', $supplier);

        if ((int) $supplier->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $supplier) {
                $supplier->update($this->validated($request));
            });
            AuditLogger::updated($supplier);

            return redirect()->route('suppliers.show', $supplier)->with('success', 'Supplier updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update supplier: '.$e->getMessage());
        }
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $this->authorize('delete', $supplier);

        if ((int) $supplier->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $supplier->delete();
        AuditLogger::deleted($supplier);

        return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'supplier_code' => ['nullable', 'string', 'max:32'],
            'tax_id' => ['nullable', 'string', 'max:64'],
            'contact_person' => ['nullable', 'string', 'max:128'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'website' => ['nullable', 'url', 'max:255'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:128'],
            'state' => ['nullable', 'string', 'max:128'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'currency_code' => ['nullable', 'string', 'max:8'],
            'status' => ['sometimes', Rule::in(array_keys(Supplier::STATUSES))],
            'payment_terms' => ['nullable', 'string', 'max:128'],
            'notes' => ['nullable', 'string'],
        ]);
    }
}
