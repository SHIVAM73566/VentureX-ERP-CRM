<x-layouts.app
    :title="'Security Events'"
    :breadcrumbs="[['label' => 'Administration', 'url' => route('security.dashboard')], ['label' => 'Security Events']]">

    <div class="mx-auto max-w-5xl space-y-6">
        <div class="card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-semibold text-ink-900">Security Events</h2>
                    <p class="text-sm text-ink-500">Filter and review security-related events across your organization.</p>
                </div>
                <a href="{{ route('security.dashboard') }}" class="btn btn-secondary btn-sm">Back to Dashboard</a>
            </div>

            <form method="GET" class="mb-4 flex flex-wrap gap-3">
                <select name="severity" class="input input-sm w-40">
                    <option value="">All severities</option>
                    <option value="info" {{ request('severity') === 'info' ? 'selected' : '' }}>Info</option>
                    <option value="warning" {{ request('severity') === 'warning' ? 'selected' : '' }}>Warning</option>
                    <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                </select>
                <input type="text" name="event" value="{{ request('event') }}" placeholder="Event type..." class="input input-sm w-48">
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </form>

            @if ($events->isEmpty())
                <p class="py-8 text-center text-sm text-ink-400">No security events found.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Event</th>
                                <th>Severity</th>
                                <th>User</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                <tr>
                                    <td class="text-xs text-ink-500">{{ $event->created_at->diffForHumans() }}</td>
                                    <td class="text-sm font-medium text-ink-900">{{ $event->event }}</td>
                                    <td>
                                        @php
                                            $colors = ['info' => 'bg-blue-100 text-blue-700', 'warning' => 'bg-yellow-100 text-yellow-700', 'high' => 'bg-orange-100 text-orange-700', 'critical' => 'bg-red-100 text-red-700'];
                                        @endphp
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $colors[$event->severity] ?? 'bg-ink-100 text-ink-600' }}">
                                            {{ $event->severity }}
                                        </span>
                                    </td>
                                    <td class="text-sm text-ink-600">{{ $event->user?->name ?? 'System' }}</td>
                                    <td class="max-w-xs truncate text-xs text-ink-500">{{ is_array($event->details) ? json_encode($event->details) : $event->details }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $events->links() }}
            @endif
        </div>
    </div>
</x-layouts.app>
