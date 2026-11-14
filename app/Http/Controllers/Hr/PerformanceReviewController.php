<?php

namespace App\Http\Controllers\Hr;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\PerformanceReview;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceReviewController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PerformanceReview::class);

        $reviews = PerformanceReview::ofCompany()
            ->with('employee', 'reviewer')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('employee'), fn ($q) => $q->where('employee_id', $request->integer('employee')))
            ->orderByDesc('review_date')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => PerformanceReview::ofCompany()->count(),
            'draft' => PerformanceReview::ofCompany()->where('status', 'draft')->count(),
            'submitted' => PerformanceReview::ofCompany()->where('status', 'submitted')->count(),
            'average' => (float) PerformanceReview::ofCompany()->where('status', '!=', 'draft')->avg('overall_score'),
        ];

        return view('hr.reviews.index', [
            'reviews' => $reviews,
            'summary' => $summary,
            'employees' => Employee::ofCompany()->active()->orderBy('first_name')->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', PerformanceReview::class);

        return view('hr.reviews.form', [
            'review' => new PerformanceReview([
                'review_date' => now()->toDateString(),
                'period' => now()->format('Y').' Q'.(int) ceil(now()->month / 3),
                'status' => 'draft',
                'employee_id' => $request->integer('employee') ?: null,
            ]),
            'employees' => Employee::ofCompany()->active()->orderBy('first_name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PerformanceReview::class);

        $data = $request->validate($this->rules());
        $data['overall_score'] = $data['overall_score'] ?? $this->averageScore($data);

        $review = PerformanceReview::create([
            ...$data,
            'company_id' => CompanyContext::id(),
            'reviewer_id' => auth()->id(),
        ]);

        AuditLogger::created($review);

        return redirect()->route('hr.reviews.show', $review)->with('success', 'Performance review created.');
    }

    public function show(PerformanceReview $review): View
    {
        $this->authorize('view', $review);
        $this->ensureCompany($review);

        $review->load('employee.department', 'reviewer');

        return view('hr.reviews.show', compact('review'));
    }

    public function edit(PerformanceReview $review): View
    {
        $this->authorize('update', $review);
        $this->ensureCompany($review);

        return view('hr.reviews.form', [
            'review' => $review,
            'employees' => Employee::ofCompany()->active()->orderBy('first_name')->get(),
        ]);
    }

    public function update(Request $request, PerformanceReview $review): RedirectResponse
    {
        $this->authorize('update', $review);
        $this->ensureCompany($review);

        $data = $request->validate($this->rules());
        $data['overall_score'] = $data['overall_score'] ?? $this->averageScore($data);

        $review->update($data);
        AuditLogger::updated($review);

        return redirect()->route('hr.reviews.show', $review)->with('success', 'Performance review updated.');
    }

    public function destroy(PerformanceReview $review): RedirectResponse
    {
        $this->authorize('delete', $review);
        $this->ensureCompany($review);

        $review->delete();
        AuditLogger::deleted($review);

        return redirect()->route('hr.reviews.index')->with('success', 'Performance review archived.');
    }

    protected function ensureCompany(PerformanceReview $review): void
    {
        if ((int) $review->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function averageScore(array $data): float
    {
        $scores = array_filter([
            (float) ($data['goals_score'] ?? 0),
            (float) ($data['competency_score'] ?? 0),
        ], fn ($value) => $value > 0);

        return $scores ? round(array_sum($scores) / count($scores), 2) : 0;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'employee_id' => ['required', CompanyContext::existsInCompany('employees')],
            'period' => ['required', 'string', 'max:40'],
            'review_date' => ['required', 'date'],
            'overall_score' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'goals_score' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'competency_score' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'status' => ['required', 'in:'.implode(',', array_keys(PerformanceReview::STATUSES))],
            'strengths' => ['nullable', 'string', 'max:4000'],
            'improvements' => ['nullable', 'string', 'max:4000'],
            'goals' => ['nullable', 'string', 'max:4000'],
            'comments' => ['nullable', 'string', 'max:4000'],
        ];
    }
}
