<x-layouts.app
    :title="'Control Center'"
    :breadcrumbs="[['label' => 'Administration'], ['label' => 'Control Center']]">

    @php
        $icons = [
            'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            'badge' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 112 0v1m-2 1a2 2 0 002 2h2a2 2 0 002-2m-6 1v5m0 0l-2-2m2 2l2-2',
            'ticket' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'bug' => 'M12 12m-3 0a3 3 0 106 0 3 3 0 10-6 0M13.73 21h-3.46M20 4v2M4 4v2m13.5-4l2 2M4 6l2-2m14 14l2 2M4 18l2-2',
            'box' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'megaphone' => 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
            'activity' => 'M22 12h-4l-3 9L9 3l-3 9H2',
            'zap' => 'M13 10V3L4 14h7v7l9-11h-7z',
        ];
    @endphp

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-navy-50 text-navy-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['users'] }}"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900">{{ $stats['total_customers'] ?? 0 }}</p>
                        <p class="text-xs text-ink-500">Total Customers</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['ticket'] }}"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900">{{ $stats['open_tickets'] ?? 0 }}</p>
                        <p class="text-xs text-ink-500">Open Tickets</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-red-50 text-red-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['bug'] }}"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900">{{ $stats['new_errors'] ?? 0 }}</p>
                        <p class="text-xs text-ink-500">New Errors</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['megaphone'] }}"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900">{{ $stats['announcements'] ?? 0 }}</p>
                        <p class="text-xs text-ink-500">Announcements</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.support.index') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium bg-blue-50 text-blue-700 hover:bg-blue-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['ticket'] }}"/></svg>
                Support Center
            </a>
            <a href="{{ route('admin.errors.index') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium bg-red-50 text-red-700 hover:bg-red-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['bug'] }}"/></svg>
                Error Center
            </a>
            <a href="{{ route('admin.control-center.customers') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium bg-navy-50 text-navy-700 hover:bg-navy-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['users'] }}"/></svg>
                Manage Customers
            </a>
            <a href="{{ route('admin.control-center.announcements') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium bg-amber-50 text-amber-700 hover:bg-amber-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['megaphone'] }}"/></svg>
                Announcements
            </a>
            <a href="{{ route('admin.control-center.updates') }}" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium bg-violet-50 text-violet-700 hover:bg-violet-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['zap'] }}"/></svg>
                Product Updates
            </a>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800">Recent Activity</h2>
                <span class="text-xs text-ink-400">Latest system-wide events</span>
            </div>
            <div class="divide-y divide-ink-100">
                @forelse ($activities as $activity)
                    <div class="flex items-start gap-3 p-4">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ match($activity['type'] ?? 'info') { 'error' => 'bg-red-100 text-red-600', 'warning' => 'bg-amber-100 text-amber-600', 'success' => 'bg-emerald-100 text-emerald-600', default => 'bg-navy-100 text-navy-600' } }}">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons['activity'] }}"/></svg>
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm text-ink-800">{{ $activity['message'] ?? 'Activity recorded' }}</p>
                            <p class="text-xs text-ink-400">{{ $activity['user'] ?? 'System' }} -- {{ $activity['time'] ?? '' }}</p>
                        </div>
                    </div>
                @empty
                    <p class="p-6 text-center text-sm text-ink-400">No recent activity.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
