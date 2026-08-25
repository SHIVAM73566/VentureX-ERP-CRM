<x-layouts.app
    :title="'Export Center'"
    :breadcrumbs="[['label' => 'Administration'], ['label' => 'Export Center']]">

    <div class="mx-auto max-w-5xl space-y-6">
        <form method="POST" action="{{ route('admin.exports.store') }}" class="card">
            @csrf
            <h2 class="mb-1 text-lg font-bold text-ink-900">Request a data export</h2>
            <p class="mb-4 text-sm text-ink-500">Sensitive datasets require super-admin approval before the export is generated. Exports are delivered as expiring, download-limited signed links.</p>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="data_type" class="label">Data type</label>
                    <select id="data_type" name="data_type" class="input" required>
                        @foreach ($sources as $source)
                            <option value="{{ $source }}">{{ ucfirst($source) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="format" class="label">Format</label>
                    <select id="format" name="format" class="input" required>
                        <option value="csv">CSV</option>
                        <option value="xlsx">Excel (XLSX)</option>
                    </select>
                </div>
                <div>
                    <label for="reason" class="label">Reason (required)</label>
                    <input id="reason" name="reason" class="input" placeholder="e.g. Q3 reconciliation" required maxlength="500">
                </div>
            </div>

            @error('data_type')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('reason')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn-accent">Request export</button>
            </div>
        </form>

        <div class="card">
            <h2 class="mb-4 text-lg font-bold text-ink-900">Export requests</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-200 text-xs uppercase tracking-wide text-ink-400">
                            <th class="py-2 pr-4">Ref</th>
                            <th class="py-2 pr-4">Data</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Requested by</th>
                            <th class="py-2 pr-4">Records</th>
                            <th class="py-2 pr-4">Expires</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($requests as $export)
                            <tr class="border-b border-ink-100">
                                <td class="py-2.5 pr-4 font-mono text-xs text-ink-600">{{ $export->export_id }}</td>
                                <td class="py-2.5 pr-4">
                                    {{ ucfirst($export->data_type) }}
                                    <span class="ml-1 badge {{ $export->sensitivity === 'restricted' ? 'badge-amber' : 'badge-gray' }}">{{ $export->sensitivity }}</span>
                                </td>
                                <td class="py-2.5 pr-4">
                                    <span class="badge {{ $export->status === 'ready' ? 'badge-green' : ($export->status === 'pending' ? 'badge-amber' : ($export->status === 'rejected' ? 'badge-red' : 'badge-gray')) }}">{{ $export->status }}</span>
                                </td>
                                <td class="py-2.5 pr-4 text-xs text-ink-600">{{ $export->user?->email ?? '—' }}</td>
                                <td class="py-2.5 pr-4">{{ $export->record_count ?: '—' }}</td>
                                <td class="py-2.5 pr-4 text-xs text-ink-500">{{ $export->expires_at?->diffForHumans() ?? '—' }}</td>
                                <td class="py-2.5">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($export->status === 'pending' && $canApprove)
                                            <form method="POST" action="{{ route('admin.exports.approve', $export) }}">
                                                @csrf
                                                <button type="submit" class="btn-secondary px-3 py-1.5 text-xs">Approve</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.exports.reject', $export) }}">
                                                @csrf
                                                <button type="submit" class="btn-danger px-3 py-1.5 text-xs">Reject</button>
                                            </form>
                                        @elseif ($export->status === 'ready')
                                            <a href="{{ route('admin.exports.download', $export) }}" class="btn-secondary px-3 py-1.5 text-xs">Download ({{ $export->download_count }}/{{ $export->max_downloads }})</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-sm text-ink-400">No export requests yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $requests->links() }}</div>
        </div>
    </div>
</x-layouts.app>
