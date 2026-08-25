<x-layouts.app
    :title="'Product Updates'"
    :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Product Updates']]">

    @php
        $typeColor = fn ($type) => match($type) {
            'patch' => 'badge-gray',
            'minor' => 'badge-blue',
            'major' => 'badge-violet',
            default => 'badge-gray',
        };
    @endphp

    <div class="mx-auto max-w-5xl space-y-6" x-data="{ showForm: false }">

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-ink-500">Manage product versions and update notifications sent to customer installations.</p>
            </div>
            <button @click="showForm = !showForm" class="btn-primary">
                <span x-show="!showForm">New Update</span>
                <span x-show="showForm">Cancel</span>
            </button>
        </div>

        <div x-show="showForm" x-cloak x-transition>
            <form method="POST" action="{{ route('admin.control-center.updates.store') }}" class="card">
                @csrf
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-ink-400">Create Product Update</h3>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="version" class="label">Version Number</label>
                        <input id="version" name="version" class="input font-mono" required placeholder="e.g. 2.4.0" maxlength="32">
                    </div>
                    <div>
                        <label for="type" class="label">Update Type</label>
                        <select id="type" name="type" class="input" required>
                            <option value="patch">Patch</option>
                            <option value="minor">Minor</option>
                            <option value="major">Major</option>
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="title" class="label">Title</label>
                        <input id="title" name="title" class="input" required maxlength="255" placeholder="Release title">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="description" class="label">Description</label>
                        <textarea id="description" name="description" class="input" rows="3" required placeholder="High-level summary of this update..."></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="changelog" class="label">Changelog</label>
                        <textarea id="changelog" name="changelog" class="input font-mono text-xs" rows="6" placeholder="- Added feature X&#10;- Fixed bug Y&#10;- Improved performance of Z"></textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="mandatory" value="1" class="rounded border-ink-300 text-navy-600 focus:ring-navy-500">
                            <span class="text-sm text-ink-700">Mandatory update (customers must update)</span>
                        </label>
                    </div>
                </div>

                @error('version')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

                <div class="mt-4 flex justify-end">
                    <button type="submit" class="btn-accent">Publish Update</button>
                </div>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800">Existing Updates</h2>
                <span class="text-xs text-ink-400">{{ $updates->count() }} total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Mandatory</th>
                            <th>Published</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($updates as $update)
                            <tr>
                                <td class="font-mono text-xs font-medium text-ink-800">{{ $update->version }}</td>
                                <td class="font-medium text-ink-800">{{ Str::limit($update->title, 40) }}</td>
                                <td><span class="{{ $typeColor($update->type) }}">{{ ucfirst($update->type) }}</span></td>
                                <td>
                                    @if ($update->mandatory)
                                        <span class="badge badge-red">Required</span>
                                    @else
                                        <span class="badge badge-gray">Optional</span>
                                    @endif
                                </td>
                                <td class="text-xs text-ink-400">{{ $update->created_at->format('d M Y') }}</td>
                                <td class="text-right">
                                    <form method="POST" action="{{ route('admin.control-center.updates.destroy', $update) }}" onsubmit="return confirm('Delete this update record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-ink-400">No product updates yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
