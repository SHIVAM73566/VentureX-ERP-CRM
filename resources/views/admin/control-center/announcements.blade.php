<x-layouts.app
    :title="'Announcements'"
    :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Announcements']]">

    @php
        $typeColor = fn ($type) => match($type) {
            'info' => 'badge-blue',
            'warning' => 'badge-yellow',
            'critical' => 'badge-red',
            'update' => 'badge-violet',
            default => 'badge-gray',
        };

        $statusColor = fn ($status) => match($status) {
            'published' => 'badge-green',
            'draft' => 'badge-gray',
            'expired' => 'badge-amber',
            default => 'badge-gray',
        };
    @endphp

    <div class="mx-auto max-w-5xl space-y-6" x-data="{ showForm: false }">

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-ink-500">Manage platform-wide announcements visible to all customers.</p>
            </div>
            <button @click="showForm = !showForm" class="btn-primary">
                <span x-show="!showForm">New Announcement</span>
                <span x-show="showForm">Cancel</span>
            </button>
        </div>

        <div x-show="showForm" x-cloak x-transition>
            <form method="POST" action="{{ route('admin.control-center.announcements.store') }}" class="card">
                @csrf
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-ink-400">Create Announcement</h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label for="title" class="label">Title</label>
                        <input id="title" name="title" class="input" required maxlength="255" placeholder="Announcement title">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="message" class="label">Message</label>
                        <textarea id="message" name="message" class="input" rows="4" required placeholder="Announcement body..."></textarea>
                    </div>
                    <div>
                        <label for="type" class="label">Type</label>
                        <select id="type" name="type" class="input" required>
                            <option value="info">Info</option>
                            <option value="warning">Warning</option>
                            <option value="critical">Critical</option>
                            <option value="update">Update</option>
                        </select>
                    </div>
                    <div>
                        <label for="target_audience" class="label">Target Audience</label>
                        <select id="target_audience" name="target_audience" class="input" required>
                            <option value="all">All Customers</option>
                            <option value="enterprise">Enterprise Only</option>
                            <option value="professional">Professional & Up</option>
                            <option value="trial">Trial Users</option>
                        </select>
                    </div>
                    <div>
                        <label for="publish_at" class="label">Publish Date</label>
                        <input id="publish_at" name="publish_at" type="datetime-local" class="input" value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div>
                        <label for="expires_at" class="label">Expires Date</label>
                        <input id="expires_at" name="expires_at" type="datetime-local" class="input">
                    </div>
                </div>

                @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('message')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-accent">Publish Announcement</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800">Existing Announcements</h2>
                <span class="text-xs text-ink-400">{{ $announcements->count() }} total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Target</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th>Expires</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($announcements as $announcement)
                            <tr>
                                <td class="font-medium text-ink-800">{{ Str::limit($announcement->title, 40) }}</td>
                                <td><span class="{{ $typeColor($announcement->type) }}">{{ ucfirst($announcement->type) }}</span></td>
                                <td class="text-xs text-ink-600">{{ ucfirst($announcement->target_audience) }}</td>
                                <td><span class="{{ $statusColor($announcement->status) }}">{{ ucfirst($announcement->status) }}</span></td>
                                <td class="text-xs text-ink-400">{{ $announcement->publish_at?->format('d M Y H:i') ?? '--' }}</td>
                                <td class="text-xs text-ink-400">{{ $announcement->expires_at?->format('d M Y H:i') ?? 'Never' }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.control-center.announcements.destroy', $announcement) }}" onsubmit="return confirm('Delete this announcement?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-sm text-ink-400">No announcements yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
