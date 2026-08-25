<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Customer;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Contact::class);

        $contacts = Contact::ofCompany()
            ->with('customer')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('customer_id'), fn ($q) => $q->where('customer_id', $request->integer('customer_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Contact::ofCompany()->count(),
            'primary' => Contact::ofCompany()->where('is_primary', true)->count(),
            'active' => Contact::ofCompany()->where('is_active', true)->count(),
        ];

        return view('crm.contacts.index', [
            'contacts' => $contacts,
            'summary' => $summary,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Contact::class);

        return view('crm.contacts.form', [
            'contact' => new Contact,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Contact::class);

        $data = $request->validate($this->rules());

        try {
            $contact = DB::transaction(function () use ($data, $request) {
                if ($request->boolean('is_primary')) {
                    Contact::ofCompany()->where('customer_id', $data['customer_id'] ?? null)->update(['is_primary' => false]);
                }

                return Contact::create([
                    'company_id' => CompanyContext::id(),
                    ...$data,
                    'is_primary' => $request->boolean('is_primary'),
                    'is_active' => $request->boolean('is_active', true),
                ]);
            });

            AuditLogger::created($contact);

            return redirect()->route('crm.contacts.index')->with('success', 'Contact created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create contact: '.$e->getMessage());
        }
    }

    public function edit(Contact $contact): View
    {
        $this->authorize('update', $contact);

        if ((int) $contact->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        return view('crm.contacts.form', [
            'contact' => $contact,
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $this->authorize('update', $contact);

        if ((int) $contact->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $data = $request->validate($this->rules());

        try {
            DB::transaction(function () use ($data, $request, $contact) {
                if ($request->boolean('is_primary')) {
                    Contact::ofCompany()->where('customer_id', $data['customer_id'] ?? $contact->customer_id)->where('id', '!=', $contact->id)->update(['is_primary' => false]);
                }

                $contact->update([...$data, 'is_primary' => $request->boolean('is_primary'), 'is_active' => $request->boolean('is_active', true)]);
            });

            AuditLogger::updated($contact);

            return redirect()->route('crm.contacts.index')->with('success', 'Contact updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update contact: '.$e->getMessage());
        }
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $this->authorize('delete', $contact);

        if ((int) $contact->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $contact->delete();
        AuditLogger::deleted($contact);

        return redirect()->route('crm.contacts.index')->with('success', 'Contact deleted.');
    }

    protected function rules(): array
    {
        return [
            'customer_id' => ['nullable', CompanyContext::existsInCompany('customers')],
            'first_name' => ['required', 'string', 'max:128'],
            'last_name' => ['nullable', 'string', 'max:128'],
            'title' => ['nullable', 'string', 'max:128'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:32'],
            'mobile' => ['nullable', 'string', 'max:32'],
        ];
    }
}
