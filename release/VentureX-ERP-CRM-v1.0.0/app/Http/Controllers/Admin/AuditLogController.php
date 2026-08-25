<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AuditLog::class);

        $companyId = CompanyContext::id();

        $logs = AuditLog::query()
            ->where('company_id', $companyId)
            ->with('user')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('module', 'like', "%{$search}%")
                        ->orWhere('event', 'like', "%{$search}%")
                        ->orWhere('record_type', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('event'), fn ($q) => $q->where('event', $request->string('event')))
            ->when($request->filled('module'), fn ($q) => $q->where('module', $request->string('module')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->integer('user_id')))
            ->when($request->filled('date'), fn ($q) => $q->whereDate('created_at', $request->string('date')))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $modules = AuditLog::query()->where('company_id', $companyId)->select('module')->distinct()->pluck('module')->filter()->sort()->values();
        $events = AuditLog::query()->where('company_id', $companyId)->select('event')->distinct()->pluck('event')->filter()->sort()->values();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'modules' => $modules,
            'events' => $events,
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function show(AuditLog $log): View
    {
        $this->authorize('viewAny', AuditLog::class);

        // IDOR protection: ensure log belongs to current user's company
        if ((int) $log->company_id !== (int) CompanyContext::id() && ! auth()->user()->hasRole('super_admin')) {
            abort(404);
        }

        $log->load('user', 'company');

        return view('admin.audit-logs.show', compact('log'));
    }
}
