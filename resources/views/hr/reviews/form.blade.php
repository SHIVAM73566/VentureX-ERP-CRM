<x-layouts.app
    :title="$review->exists ? 'Edit Review' : 'New Review'"
    :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Performance', 'url' => route('hr.reviews.index')], ['label' => $review->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-3xl">
        <form method="POST" action="{{ $review->exists ? route('hr.reviews.update', $review) : route('hr.reviews.store') }}" class="space-y-6">
            @csrf
            @if ($review->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Review Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Employee *" name="employee_id" :options="$employees->mapWithKeys(fn ($e) => [$e->id => $e->fullName().' ('.$e->employee_number.')'])" :value="$review->employee_id" required />
                    <x-form.input label="Period *" name="period" :value="$review->period" placeholder="e.g. 2026 Q4" required />
                    <x-form.input label="Review Date *" name="review_date" type="date" :value="$review->review_date?->toDateString()" required />
                    <x-form.select label="Status *" name="status" :options="\App\Models\PerformanceReview::STATUSES" :value="$review->status ?? 'draft'" required />
                </div>
                <div class="grid gap-4 sm:grid-cols-3">
                    <x-form.input label="Goals Score (0–5)" name="goals_score" type="number" step="0.01" min="0" max="5" :value="$review->goals_score" />
                    <x-form.input label="Competency Score (0–5)" name="competency_score" type="number" step="0.01" min="0" max="5" :value="$review->competency_score" />
                    <x-form.input label="Overall Score (0–5)" name="overall_score" type="number" step="0.01" min="0" max="5" :value="$review->overall_score" help="Leave blank to average the two scores." />
                </div>
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Narrative</h2>
                <x-form.textarea label="Strengths" name="strengths" :value="$review->strengths" rows="3" />
                <x-form.textarea label="Improvements" name="improvements" :value="$review->improvements" rows="3" />
                <x-form.textarea label="Goals for next period" name="goals" :value="$review->goals" rows="3" />
                <x-form.textarea label="Comments" name="comments" :value="$review->comments" rows="2" />
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('hr.reviews.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $review->exists ? 'Save Changes' : 'Create Review' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
