<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Customer;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        $customers = Customer::ofCompany()
            ->withCount('contacts', 'opportunities')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('tax_id', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('crm.customers.index', ['customers' => $customers]);
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('crm.customers.form', [
            'customer' => new Customer,
            'countries' => Country::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $this->authorize('create', Customer::class);

        $customer = Customer::create([
            ...$request->validated(),
            'company_id' => CompanyContext::id(),
            'created_by' => auth()->id(),
        ]);

        AuditLogger::created($customer);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer created.');
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        $customer->load('contacts', 'opportunities', 'country');

        return view('crm.customers.show', [
            'customer' => $customer,
            'activities' => $customer->activities()->latest()->limit(20)->get(),
        ]);
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('crm.customers.form', [
            'customer' => $customer,
            'countries' => Country::orderBy('name')->get(),
            'branches' => Branch::orderBy('name')->get(),
        ]);
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $customer->update($request->validated());
        AuditLogger::updated($customer);

        return redirect()->route('customers.show', $customer)->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $customer->delete();
        AuditLogger::deleted($customer);

        return redirect()->route('customers.index')->with('success', 'Customer deleted.');
    }
}
