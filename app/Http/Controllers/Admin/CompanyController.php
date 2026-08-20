<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Country;
use App\Services\AuditLogger;
use App\Services\SecurityEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Company::class);

        return view('admin.companies.index', ['companies' => Company::withCount('users')->paginate(10)]);
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('admin.companies.form', ['company' => new Company, 'countries' => Country::orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $data = $this->validated($request);
        $company = Company::create($data);
        AuditLogger::created($company);

        return redirect()->route('admin.companies.edit', $company)
            ->with('success', 'Company created.');
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        return view('admin.companies.form', ['company' => $company, 'countries' => Country::orderBy('name')->get()]);
    }

    public function update(Request $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $oldValues = $company->only([
            'name', 'legal_name', 'email', 'phone', 'tax_id', 'registration_number',
            'currency_code', 'fiscal_year_start', 'is_active',
        ]);

        $company->update($this->validated($request));

        $newValues = $company->only(array_keys($oldValues));

        AuditLogger::updated($company, $oldValues);

        $sensitiveChanged = array_diff_assoc($oldValues, $newValues);
        if ($sensitiveChanged && auth()->user()) {
            SecurityEventService::log(
                'sensitive_data_changed',
                'Sensitive company fields modified by '.auth()->user()->email.'. Changed: '.implode(', ', array_keys($sensitiveChanged)),
                'high'
            );
        }

        return back()->with('success', 'Company updated.');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        $company->delete();
        AuditLogger::deleted($company);

        return redirect()->route('admin.companies.index')->with('success', 'Company deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'tax_id' => ['nullable', 'string', 'max:64'],
            'registration_number' => ['nullable', 'string', 'max:64'],
            'industry' => ['nullable', 'string', 'max:128'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:128'],
            'state' => ['nullable', 'string', 'max:128'],
            'postal_code' => ['nullable', 'string', 'max:16'],
            'country_id' => ['nullable', 'exists:countries,id'],
            'currency_code' => ['nullable', 'string', 'max:8'],
            'timezone' => ['nullable', 'string', 'max:64'],
            'fiscal_year_start' => ['nullable', 'date_format:Y-m-d'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }
}
