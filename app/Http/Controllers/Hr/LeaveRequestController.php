<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', LeaveRequest::class);

        $requests = LeaveRequest::ofCompany()
            ->with('employee', 'approver')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('employee'), fn ($q) => $q->where('employee_id', $request->integer('employee')))
            ->orderByDesc('start_date')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'pending' => LeaveRequest::ofCompany()->where('status', 'pending')->count(),
            'approved' => LeaveRequest::ofCompany()->where('status', 'approved')->count(),
            'rejected' => LeaveRequest::ofCompany()->where('status', 'rejected')->count(),
            'days_this_year' => (float) LeaveRequest::ofCompany()
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)
                ->sum('days'),
        ];

        return view('hr.leave.index', [
            'requests' => $requests,
            'summary' => $summary,
            'employees' => Employee::ofCompany()->active()->orderBy('first_name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', LeaveRequest::class);

        return view('hr.leave.form', [
            'leave' => new LeaveRequest([
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
                'type' => 'annual',
                'employee_id' => $request->integer('employee') ?: null,
            ]),
            'employees' => Employee::ofCompany()->active()->orderBy('first_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LeaveRequest::class);

        $data = $request->validate($this->rules());
        $data['days'] = $data['days'] ?? $this->calculateDays($data['start_date'], $data['end_date'], (bool) ($data['half_day'] ?? false));

        $leave = LeaveRequest::create([
            ...$data,
            'company_id' => CompanyContext::id(),
            'status' => 'pending',
        ]);

        AuditLogger::created($leave);

        return redirect()->route('hr.leave.index')->with('success', 'Leave request submitted.');
    }

    public function approve(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorize('update', $leave);
        $this->ensureCompany($leave);

        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'decided_at' => now(),
            'decision_notes' => $request->input('decision_notes'),
        ]);

        AuditLogger::updated($leave);

        return back()->with('success', 'Leave request approved.');
    }

    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        $this->authorize('update', $leave);
        $this->ensureCompany($leave);

        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'decided_at' => now(),
            'decision_notes' => $request->input('decision_notes'),
        ]);

        AuditLogger::updated($leave);

        return back()->with('success', 'Leave request rejected.');
    }

    public function destroy(LeaveRequest $leave): RedirectResponse
    {
        $this->authorize('delete', $leave);
        $this->ensureCompany($leave);

        $leave->delete();
        AuditLogger::deleted($leave);

        return redirect()->route('hr.leave.index')->with('success', 'Leave request removed.');
    }

    protected function ensureCompany(LeaveRequest $leave): void
    {
        if ((int) $leave->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }
    }

    protected function calculateDays(string $start, string $end, bool $halfDay = false): float
    {
        if ($halfDay) {
            return 0.5;
        }

        $from = Carbon::parse($start);
        $to = Carbon::parse($end);

        return max(1, $from->diffInDays($to) + 1);
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'employee_id' => ['required', CompanyContext::existsInCompany('employees')],
            'type' => ['required', 'in:'.implode(',', array_keys(LeaveRequest::TYPES))],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'days' => ['nullable', 'numeric', 'min:0.5', 'max:366'],
            'half_day' => ['boolean'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
