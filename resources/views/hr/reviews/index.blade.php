<x-layouts.app title="Performance Reviews" :breadcrumbs="[['label' => 'Human Resources', 'url' => route('hr.dashboard')], ['label' => 'Performance']]">

    <x-slot name="actions">
        @can('create', App\Models\PerformanceReview::class)
            <a href="{{ route('hr.reviews.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Review
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-dashboard.stat-card label="Total Reviews" :value="$summary['total']" icon="document" color="blue" />
        <x-dashboard.stat-card label="Draft" :value="$summary['draft']" icon="pencil" color="amber" />
        <x-dashboard.stat-card label="Submitted" :value="$summary['submitted']" icon="badge" color="green" />
        <x-dashboard.stat-card label="Average Score" :value="number_format($summary['average'], 2).' / 5'" icon="chart" color="violet" />
    </div>

    <form method="GET" action="{{ route('hr.reviews.index') }}" class="mt-6 mb-4 flex flex-wrap gap-2">
        <select name="employee" class="input max-w-[14rem]" onchange="this.form.submit()">
            <option value="">All employees</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected((string) request('employee') === (string) $employee->id)>{{ $employee->fullName() }}</option>
            @endforeach
        </select>
        <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
            <option value="">All statuses</option>
            @foreach (\App\Models\PerformanceReview::STATUSES as $key => $label)
                <option value="{{ $key }}" @selected(request('status') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
    </form>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Employee</th><th>Period</th><th>Overall</th><th>Goals</th><th>Competency</th><th>Rating</th><th>Status</th><th>Date</th><th></th></tr></thead>
                <tbody>
                    @forelse ($reviews as $review)
                        <tr>
                            <td class="font-medium text-ink-800">{{ $review->employee?->fullName() ?? '—' }}</td>
                            <td>{{ $review->period }}</td>
                            <td class="font-semibold">{{ number_format((float) $review->overall_score, 2) }}</td>
                            <td>{{ number_format((float) $review->goals_score, 2) }}</td>
                            <td>{{ number_format((float) $review->competency_score, 2) }}</td>
                            <td>{{ $review->rating() }}</td>
                            <td><span class="badge-{{ $review->status === 'acknowledged' ? 'green' : ($review->status === 'submitted' ? 'blue' : 'gray') }}">{{ \App\Models\PerformanceReview::STATUSES[$review->status] ?? $review->status }}</span></td>
                            <td>{{ $review->review_date?->format('d M Y') }}</td>
                            <td class="text-right"><a href="{{ route('hr.reviews.show', $review) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="py-8 text-center text-ink-400">No performance reviews found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $reviews->links() }}</div>
</x-layouts.app>
