<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\ErrorReport;
use App\Models\ProductUpdate;
use App\Models\SupportTicket;
use App\Models\SystemAnnouncement;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminControlCenter extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $stats = [
            'total_customers' => Company::count(),
            'open_tickets' => SupportTicket::where('status', 'open')->count(),
            'total_tickets' => SupportTicket::count(),
            'new_errors' => ErrorReport::where('status', 'new')->count(),
            'total_errors' => ErrorReport::count(),
            'announcements' => SystemAnnouncement::where('is_active', true)->count(),
        ];

        $activities = AuditLog::latest()
            ->limit(10)
            ->get()
            ->map(fn ($log) => [
                'type' => 'info',
                'message' => $log->action.' on '.($log->auditable_type ?? 'system'),
                'user' => $log->user->name ?? 'System',
                'time' => $log->created_at->diffForHumans(),
            ]);

        return view('admin.control-center.index', compact('stats', 'activities'));
    }

    public function customers(): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $companies = Company::withCount('users')
            ->get()
            ->map(function ($company) {
                $company->ticket_count = SupportTicket::where('company_id', $company->id)->count();
                $company->open_ticket_count = SupportTicket::where('company_id', $company->id)
                    ->where('status', 'open')
                    ->count();

                return $company;
            });

        return view('admin.control-center.customers', compact('companies'));
    }

    public function customer(Company $company): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        if (! auth()->user()->hasRole('super_admin') && auth()->user()->company_id !== $company->id) {
            abort(403, 'Unauthorized access to company data.');
        }

        $company->load('users');
        $company->loadCount('users');

        $tickets = SupportTicket::where('company_id', $company->id)
            ->with('user', 'assignee')
            ->latest()
            ->paginate(15);

        $errors = ErrorReport::where('company_id', $company->id)
            ->latest()
            ->paginate(15);

        return view('admin.control-center.customer', compact(
            'company', 'tickets', 'errors'
        ));
    }

    public function announcements(): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $announcements = SystemAnnouncement::with('creator')
            ->latest()
            ->paginate(15);

        return view('admin.control-center.announcements', compact('announcements'));
    }

    public function storeAnnouncement(Request $request): RedirectResponse
    {
        $this->authorize('create', SystemAnnouncement::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(SystemAnnouncement::TYPES))],
            'target' => ['required', 'string', 'in:'.implode(',', array_keys(SystemAnnouncement::TARGETS))],
            'target_companies' => ['nullable', 'array'],
            'target_companies.*' => ['integer', 'exists:companies,id'],
            'published_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:published_at'],
        ]);

        $validated['created_by'] = auth()->id();
        $validated['is_active'] = true;

        if (! isset($validated['published_at'])) {
            $validated['published_at'] = now();
        }

        $announcement = SystemAnnouncement::create($validated);

        AuditLogger::created($announcement);

        return back()->with('success', 'Announcement published.');
    }

    public function updates(): View
    {
        $this->authorize('viewAny', SupportTicket::class);

        $updates = ProductUpdate::with('creator')
            ->latest()
            ->paginate(15);

        return view('admin.control-center.updates', compact('updates'));
    }

    public function storeUpdate(Request $request): RedirectResponse
    {
        $this->authorize('create', ProductUpdate::class);

        $validated = $request->validate([
            'version' => ['required', 'string', 'max:32'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],
            'changelog' => ['nullable', 'string', 'max:10000'],
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(ProductUpdate::TYPES))],
            'min_php_version' => ['nullable', 'string', 'max:32'],
            'min_laravel_version' => ['nullable', 'string', 'max:32'],
            'download_url' => ['nullable', 'url', 'max:500'],
            'is_mandatory' => ['sometimes', 'boolean'],
            'released_at' => ['nullable', 'date'],
        ]);

        $validated['created_by'] = auth()->id();

        if (! isset($validated['released_at'])) {
            $validated['released_at'] = now();
        }

        $update = ProductUpdate::create($validated);

        AuditLogger::created($update);

        return back()->with('success', 'Product update created.');
    }

    public function destroyAnnouncement(SystemAnnouncement $announcement): RedirectResponse
    {
        $this->authorize('delete', $announcement);

        $announcement->delete();

        AuditLogger::deleted($announcement);

        return back()->with('success', 'Announcement deleted.');
    }

    public function destroyUpdate(ProductUpdate $update): RedirectResponse
    {
        $this->authorize('delete', $update);

        $update->delete();

        AuditLogger::deleted($update);

        return back()->with('success', 'Product update deleted.');
    }
}
