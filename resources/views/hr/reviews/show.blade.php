<x-layouts.app
    :title="'Review · '.($review->employee?->fullName() ?? 'Employee')"
    :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Performance', 'url' => route('hr.reviews.index')], ['label' => $review->period]]">

    <x-slot name="actions">
        @can('update', $review)
            <a href="{{ route('hr.reviews.edit', $review) }}" class="btn-secondary">Edit</a>
        @endcan
        @can('delete', $review)
            <form method="POST" action="{{ route('hr.reviews.destroy', $review) }}" onsubmit="return confirm('Archive this performance review?')">
                @csrf @method('DELETE')
                <button class="btn-secondary text-red-600">Archive</button>
            </form>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <div class="flex items-center gap-4">
                <span class="flex h-14 w-14 items-center justify-center rounded-full bg-navy-100 text-lg font-bold text-navy-700">{{ $review->employee?->initials() ?? '—' }}</span>
                <div class="min-w-0">
                    <h2 class="truncate text-lg font-bold text-ink-900">{{ $review->employee?->fullName() ?? '—' }}</h2>
                    <p class="text-sm text-ink-500">{{ $review->employee?->job_title ?? '—' }}</p>
                </div>
            </div>
            <dl class="mt-5 space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Period</dt><dd class="font-medium">{{ $review->period }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Review date</dt><dd>{{ $review->review_date?->format('d M Y') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Reviewer</dt><dd>{{ $review->reviewer?->displayName() ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="badge-{{ $review->status === 'acknowledged' ? 'green' : ($review->status === 'submitted' ? 'blue' : 'gray') }}">{{ \App\Models\PerformanceReview::STATUSES[$review->status] ?? $review->status }}</span></dd></div>
            </dl>
        </div>

        <div class="card lg:col-span-2">
            <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-ink-400">Scores</h3>
            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ([['Overall', $review->overall_score, 'text-violet-600'], ['Goals', $review->goals_score, 'text-navy-600'], ['Competency', $review->competency_score, 'text-emerald-600']] as [$label, $score, $color])
                    <div class="rounded-xl border border-ink-100 bg-ink-50/50 p-4 text-center dark:border-ink-800 dark:bg-ink-800/40">
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-400">{{ $label }}</p>
                        <p class="mt-1 text-3xl font-bold tabular-nums {{ $color }}">{{ number_format((float) $score, 2) }}</p>
                        <p class="text-xs text-ink-400">out of 5.00</p>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex items-center justify-between rounded-lg bg-navy-50 px-4 py-3 dark:bg-navy-900/40">
                <span class="text-sm font-medium text-ink-600 dark:text-ink-300">Overall rating</span>
                <span class="text-sm font-bold text-navy-700 dark:text-navy-300">{{ $review->rating() }}</span>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        @foreach ([['Strengths', $review->strengths], ['Areas for improvement', $review->improvements], ['Goals for next period', $review->goals], ['Reviewer comments', $review->comments]] as [$heading, $body])
            <div class="card">
                <h3 class="text-sm font-bold text-ink-800 dark:text-ink-100">{{ $heading }}</h3>
                <p class="mt-2 whitespace-pre-line text-sm text-ink-600 dark:text-ink-300">{{ $body ?: 'No notes recorded.' }}</p>
            </div>
        @endforeach
    </div>
</x-layouts.app>
