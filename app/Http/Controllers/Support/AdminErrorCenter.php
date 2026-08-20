<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\ErrorReport;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminErrorCenter extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', ErrorReport::class);

        $query = ErrorReport::query()->ofCompany()->with('user');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('error_type')) {
            $query->where('error_type', $request->string('error_type'));
        }

        if ($request->filled('module')) {
            $query->where('module', $request->string('module'));
        }

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->where(function ($q) use ($search) {
                $q->where('error_message', 'like', "%{$search}%")
                    ->orWhere('file', 'like', "%{$search}%")
                    ->orWhere('error_hash', 'like', "%{$search}%");
            });
        }

        $errors = $query->latest()->paginate(25)->withQueryString();

        $companyId = auth()->user()->company_id;
        $stats = [
            'total' => ErrorReport::where('company_id', $companyId)->count(),
            'new' => ErrorReport::where('company_id', $companyId)->where('status', 'new')->count(),
            'investigating' => ErrorReport::where('company_id', $companyId)->where('status', 'investigating')->count(),
            'confirmed' => ErrorReport::where('company_id', $companyId)->where('status', 'confirmed')->count(),
            'fixed' => ErrorReport::where('company_id', $companyId)->where('status', 'fixed')->count(),
        ];

        $modules = ['CRM', 'Sales', 'Inventory', 'Procurement', 'Finance', 'Logistics', 'AI', 'Support', 'Admin'];

        return view('admin.errors.index', compact('errors', 'stats', 'modules'));
    }

    public function show(ErrorReport $error): View
    {
        // SECURITY: Verify error report belongs to user's company
        if ($error->company_id !== auth()->user()->company_id && ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized access to error report.');
        }

        $error->load('user');

        return view('admin.errors.show', compact('error'));
    }

    public function diagnose(Request $request, ErrorReport $error): RedirectResponse
    {
        // SECURITY: Verify error report belongs to user's company
        if ($error->company_id !== auth()->user()->company_id && ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized access to error report.');
        }

        $validated = $request->validate([
            'diagnosis' => ['required', 'string', 'max:5000'],
            'status' => ['sometimes', Rule::in(array_keys(ErrorReport::STATUSES))],
        ]);

        $oldValues = $error->only(['diagnosis', 'status']);

        $updateData = ['diagnosis' => $validated['diagnosis']];

        if (isset($validated['status'])) {
            $updateData['status'] = $validated['status'];
        }

        $error->update($updateData);

        AuditLogger::updated($error, $oldValues);

        return back()->with('success', 'Diagnosis saved.');
    }

    public function resolve(Request $request, ErrorReport $error): RedirectResponse
    {
        // SECURITY: Verify error report belongs to user's company
        if ($error->company_id !== auth()->user()->company_id && ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized access to error report.');
        }

        $validated = $request->validate([
            'fix_applied' => ['required', 'string', 'max:5000'],
        ]);

        $error->update([
            'status' => 'fixed',
            'fix_applied' => $validated['fix_applied'],
            'fixed_at' => now(),
        ]);

        AuditLogger::action($error, 'error_resolved', 'error_reports');

        return back()->with('success', 'Error marked as fixed.');
    }

    public function update(Request $request, ErrorReport $error): RedirectResponse
    {
        if ($error->company_id !== auth()->user()->company_id && ! auth()->user()->hasRole('super_admin')) {
            abort(403, 'Unauthorized access to error report.');
        }

        $validated = $request->validate([
            'diagnosis' => ['sometimes', 'string', 'max:5000'],
            'resolution' => ['sometimes', 'string', 'max:5000'],
            'action' => ['sometimes', 'string', 'in:investigating,ignored'],
        ]);

        $updateData = collect($validated)->except('action')->filter()->toArray();

        if (isset($validated['action'])) {
            $updateData['status'] = $validated['action'];
        }

        if (isset($validated['resolution']) && ! isset($validated['action'])) {
            $updateData['status'] = 'fixed';
            $updateData['fixed_at'] = now();
        }

        $error->update($updateData);

        AuditLogger::updated($error, []);

        return back()->with('success', 'Error report updated.');
    }
}
