<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\PayrollRun;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\Hr\PayrollService;
use App\Services\NumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function __construct(protected PayrollService $payroll) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', PayrollRun::class);

        $runs = PayrollRun::ofCompany()
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->orderByDesc('period_end')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'runs' => PayrollRun::ofCompany()->count(),
            'draft' => PayrollRun::ofCompany()->where('status', 'draft')->count(),
            'approved' => PayrollRun::ofCompany()->where('status', 'approved')->count(),
            'net_this_year' => (float) PayrollRun::ofCompany()
                ->whereYear('period_end', now()->year)
                ->whereIn('status', ['approved', 'paid'])
                ->sum('net_total'),
        ];

        return view('hr.payroll.index', compact('runs', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create', PayrollRun::class);

        return view('hr.payroll.form', [
            'run' => new PayrollRun([
                'period_start' => now()->startOfMonth()->toDateString(),
                'period_end' => now()->endOfMonth()->toDateString(),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PayrollRun::class);

        $data = $request->validate($this->rules());

        try {
            $run = DB::transaction(function () use ($data) {
                $run = PayrollRun::create([
                    ...$data,
                    'company_id' => CompanyContext::id(),
                    'reference' => NumberGenerator::next('PR', 'payroll_runs'),
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                return $this->payroll->generate($run);
            });

            AuditLogger::created($run);

            return redirect()->route('hr.payroll.show', $run)->with('success', 'Payroll run generated.');
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to generate payroll: '.$e->getMessage());
        }
    }

    public function show(PayrollRun $run): View
    {
        $this->authorize('view', $run);
        $this->ensureCompany($run);

        $run->load('items.employee.department', 'createdBy', 'approvedBy');

        return view('hr.payroll.show', compact('run'));
    }

    public function approve(PayrollRun $run): RedirectResponse
    {
        $this->authorize('approve', $run);
        $this->ensureCompany($run);

        $this->payroll->approve($run, auth()->id());
        AuditLogger::updated($run);

        return back()->with('success', 'Payroll run approved.');
    }

    public function markPaid(PayrollRun $run): RedirectResponse
    {
        $this->authorize('approve', $run);
        $this->ensureCompany($run);

        $this->payroll->markPaid($run);
        AuditLogger::updated($run);

        return back()->with('success', 'Payroll run marked as paid.');
    }

    public function regenerate(PayrollRun $run): RedirectResponse
    {
        $this->authorize('update', $run);
        $this->ensureCompany($run);

        if ($run->status !== 'draft') {
            return back()->with('error', 'Only draft payroll runs can be regenerated.');
        }

        $this->payroll->generate($run);
        AuditLogger::updated($run);

        return back()->with('success', 'Payroll run recalculated.');
    }

    public function destroy(PayrollRun $run): RedirectResponse
    {
        $this->authorize('delete', $run);
        $this->ensureCompany($run);

        $run->delete();
        AuditLogger::deleted($run);

        return redirect()->route('hr.payroll.index')->with('success', 'Payroll run archived.');
    }

    protected function ensureCompany(PayrollRun $run): void
    {
        if ((int) $run->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
