<x-layouts.app
    :title="'Error Detail'"
    :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.errors.index')], ['label' => 'Error Center', 'url' => route('admin.errors.index')], ['label' => $error->error_type]]">

    @php
        $errorStatusColor = fn ($status) => match($status) {
            'new' => 'badge-red',
            'investigating' => 'badge-yellow',
            'fixed' => 'badge-green',
            'ignored' => 'badge-gray',
            default => 'badge-gray',
        };
    @endphp

    <div class="mx-auto max-w-6xl space-y-6">

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">

                <div class="card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-red">{{ $error->error_type }}</span>
                                <span class="{{ $errorStatusColor($error->status) }}">{{ ucfirst($error->status) }}</span>
                            </div>
                            <p class="mt-2 text-sm text-ink-500">{{ $error->error_message }}</p>
                        </div>
                        <span class="text-xs text-ink-400">First seen {{ $error->created_at->diffForHumans() }}</span>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                        <div class="flex justify-between"><span class="text-ink-400">Module</span><span class="badge badge-gray">{{ $error->module ?? '--' }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-400">Controller</span><span class="font-mono text-xs text-ink-600">{{ $error->controller ?? '--' }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-400">Action</span><span class="font-mono text-xs text-ink-600">{{ $error->action ?? '--' }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-400">Route</span><span class="font-mono text-xs text-ink-600">{{ $error->route ?? '--' }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-400">HTTP Method</span><span class="font-mono text-xs text-ink-600">{{ $error->http_method ?? '--' }}</span></div>
                        <div class="flex justify-between"><span class="text-ink-400">HTTP Status</span><span class="font-mono text-xs text-ink-600">{{ $error->http_status ?? '--' }}</span></div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Stack Trace</h3>
                    <div class="overflow-x-auto rounded-lg bg-ink-950 p-4">
                        <pre class="font-mono text-xs text-emerald-400 whitespace-pre-wrap break-words">{{ $error->stack_trace ?? 'No stack trace available.' }}</pre>
                    </div>
                </div>

                @if ($error->request_data)
                    <div class="card">
                        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Request Data (Sanitized)</h3>
                        <div class="overflow-x-auto rounded-lg bg-ink-50 p-4">
                            <pre class="font-mono text-xs text-ink-700 whitespace-pre-wrap break-words">{{ is_array($error->request_data) ? json_encode($error->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $error->request_data }}</pre>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-bold text-ink-800">Diagnosis</h3>
                    </div>
                    <div class="p-5">
                        <form method="POST" action="{{ route('admin.errors.update', $error) }}">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="diagnosis" class="label">Diagnosis Notes</label>
                                <textarea id="diagnosis" name="diagnosis" class="input" rows="4" placeholder="Describe the root cause, investigation notes, or any findings...">{{ $error->diagnosis }}</textarea>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <button type="submit" class="btn-primary">Save Diagnosis</button>
                                <button type="submit" name="action" value="investigating" class="btn-secondary">Mark Investigating</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-bold text-ink-800">Resolution</h3>
                    </div>
                    <div class="p-5">
                        <form method="POST" action="{{ route('admin.errors.update', $error) }}">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="resolution" class="label">Fix Description</label>
                                <textarea id="resolution" name="resolution" class="input" rows="4" placeholder="Describe the fix applied...">{{ $error->resolution }}</textarea>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <button type="submit" class="btn-accent">Save & Mark Fixed</button>
                                <button type="submit" name="action" value="ignored" class="btn-secondary">Mark Ignored</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Error Info</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-400">Type</dt><dd><span class="badge badge-red">{{ $error->error_type }}</span></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="{{ $errorStatusColor($error->status) }}">{{ ucfirst($error->status) }}</span></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Occurrences</dt><dd class="font-medium text-ink-800">{{ $error->occurrence_count }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">First Seen</dt><dd class="text-ink-600">{{ $error->created_at->format('d M Y H:i') }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Last Seen</dt><dd class="text-ink-600">{{ $error->last_seen_at?->format('d M Y H:i') ?? '--' }}</dd></div>
                    </dl>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Server Info</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-400">App Version</dt><dd class="font-mono text-xs text-ink-600">{{ $error->app_version ?? '--' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">PHP Version</dt><dd class="font-mono text-xs text-ink-600">{{ $error->php_version ?? '--' }}</dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Laravel Version</dt><dd class="font-mono text-xs text-ink-600">{{ $error->laravel_version ?? '--' }}</dd></div>
                    </dl>
                </div>

                @if ($error->ticket)
                    <div class="card">
                        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Related Ticket</h3>
                        <a href="{{ route('admin.support.tickets.show', $error->ticket) }}" class="flex items-center gap-3 rounded-lg border border-ink-100 p-3 hover:bg-ink-50">
                            <span class="badge badge-blue">{{ $error->ticket->ticket_number }}</span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-ink-800">{{ $error->ticket->subject }}</p>
                                <p class="text-xs text-ink-400">{{ ucfirst($error->ticket->status) }}</p>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
