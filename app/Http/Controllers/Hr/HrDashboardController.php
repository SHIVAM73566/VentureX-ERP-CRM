<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\OnboardingTask;
use App\Models\PayrollRun;
use App\Models\PerformanceReview;
use App\Services\CompanyContext;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HrDashboardController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Employee::class);

        $companyId = CompanyContext::id();

        $headcount = Employee::ofCompany($companyId)->active()->count();
        $totalEmployees = Employee::ofCompany($companyId)->count();
        $onLeave = Employee::ofCompany($companyId)->where('status', 'on_leave')->count();
        $newHires = Employee::ofCompany($companyId)
            ->whereBetween('hire_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->count();

        $todayAttendance = Attendance::ofCompany($companyId)
            ->whereDate('attendance_date', now()->toDateString())
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $pendingLeave = LeaveRequest::ofCompany($companyId)->where('status', 'pending')->count();

        $lastPayroll = PayrollRun::ofCompany($companyId)->latest('period_end')->first();

        $avgPerformance = (float) PerformanceReview::ofCompany($companyId)
            ->where('status', '!=', 'draft')
            ->avg('overall_score');

        $summary = [
            'headcount' => $headcount,
            'total_employees' => $totalEmployees,
            'on_leave' => $onLeave,
            'new_hires' => $newHires,
            'present_today' => (int) (($todayAttendance['present'] ?? 0) + ($todayAttendance['remote'] ?? 0)),
            'pending_leave' => $pendingLeave,
            'avg_performance' => $avgPerformance,
            'last_payroll_net' => (float) ($lastPayroll?->net_total ?? 0),
        ];

        $departments = Department::ofCompany($companyId)
            ->withCount('employees')
            ->orderBy('name')
            ->get();

        $pendingRequests = LeaveRequest::ofCompany($companyId)
            ->with('employee')
            ->where('status', 'pending')
            ->orderBy('start_date')
            ->limit(6)
            ->get();

        $onboarding = OnboardingTask::ofCompany($companyId)
            ->with('employee')
            ->whereIn('status', ['pending', 'in_progress', 'blocked'])
            ->orderBy('due_date')
            ->limit(6)
            ->get();

        $recentReviews = PerformanceReview::ofCompany($companyId)
            ->with('employee')
            ->latest('review_date')
            ->limit(5)
            ->get();

        return view('hr.dashboard', compact(
            'summary', 'departments', 'pendingRequests', 'onboarding', 'recentReviews', 'lastPayroll'
        ));
    }
}
