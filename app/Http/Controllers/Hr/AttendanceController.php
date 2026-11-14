<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);

        $date = $request->date('date')?->toDateString() ?? now()->toDateString();

        $records = Attendance::ofCompany()
            ->with('employee.department')
            ->whereDate('attendance_date', $date)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('employee'), fn ($q) => $q->where('employee_id', $request->integer('employee')))
            ->orderBy('employee_id')
            ->paginate(25)
            ->withQueryString();

        $summary = [
            'present' => Attendance::ofCompany()->whereDate('attendance_date', $date)->whereIn('status', ['present', 'remote'])->count(),
            'late' => Attendance::ofCompany()->whereDate('attendance_date', $date)->where('status', 'late')->count(),
            'absent' => Attendance::ofCompany()->whereDate('attendance_date', $date)->where('status', 'absent')->count(),
            'leave' => Attendance::ofCompany()->whereDate('attendance_date', $date)->where('status', 'leave')->count(),
        ];

        return view('hr.attendance.index', [
            'records' => $records,
            'summary' => $summary,
            'date' => $date,
            'employees' => Employee::ofCompany()->active()->orderBy('first_name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Attendance::class);

        return view('hr.attendance.form', [
            'attendance' => new Attendance([
                'attendance_date' => $request->date('date')?->toDateString() ?? now()->toDateString(),
                'status' => 'present',
            ]),
            'employees' => Employee::ofCompany()->active()->orderBy('first_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Attendance::class);

        $data = $request->validate($this->rules());

        $exists = Attendance::ofCompany()
            ->where('employee_id', $data['employee_id'])
            ->whereDate('attendance_date', $data['attendance_date'])
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Attendance for this employee and date already exists.');
        }

        $attendance = Attendance::create([
            ...$data,
            'company_id' => CompanyContext::id(),
            'recorded_by' => auth()->id(),
        ]);

        AuditLogger::created($attendance);

        return redirect()->route('hr.attendance.index', ['date' => $attendance->attendance_date?->toDateString()])
            ->with('success', 'Attendance recorded.');
    }

    public function edit(Attendance $attendance): View
    {
        $this->authorize('update', $attendance);
        $this->ensureCompany($attendance);

        return view('hr.attendance.form', [
            'attendance' => $attendance,
            'employees' => Employee::ofCompany()->active()->orderBy('first_name')->get(),
        ]);
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $this->authorize('update', $attendance);
        $this->ensureCompany($attendance);

        $attendance->update($request->validate($this->rules()));
        AuditLogger::updated($attendance);

        return redirect()->route('hr.attendance.index', ['date' => $attendance->attendance_date?->toDateString()])
            ->with('success', 'Attendance updated.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $this->authorize('delete', $attendance);
        $this->ensureCompany($attendance);

        $attendance->delete();
        AuditLogger::deleted($attendance);

        return redirect()->route('hr.attendance.index')->with('success', 'Attendance removed.');
    }

    protected function ensureCompany(Attendance $attendance): void
    {
        if ((int) $attendance->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'employee_id' => ['required', CompanyContext::existsInCompany('employees')],
            'attendance_date' => ['required', 'date'],
            'check_in' => ['nullable', 'date_format:H:i'],
            'check_out' => ['nullable', 'date_format:H:i', 'after:check_in'],
            'status' => ['required', 'in:'.implode(',', array_keys(Attendance::STATUSES))],
            'worked_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'overtime_hours' => ['nullable', 'numeric', 'min:0', 'max:24'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
