<x-layouts.app
    :title="'Document Manager'"
    :breadcrumbs="[['label' => 'Administration'], ['label' => 'Document Manager']]">

    <div class="mx-auto max-w-5xl space-y-6">
        <form method="POST" action="{{ route('admin.documents.store') }}" enctype="multipart/form-data" class="card">
            @csrf
            <h2 class="mb-1 text-lg font-bold text-ink-900">Upload a document</h2>
            <p class="mb-4 text-sm text-ink-500">Files are size-limited, type-checked by content signature, and scanned. Suspicious files are quarantined.</p>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="title" class="label">Title</label>
                    <input id="title" name="title" class="input" required>
                </div>
                <div>
                    <label for="file" class="label">File (PDF, Office, CSV, TXT, images — max {{ round(config('security.upload.max_size_kb') / 1024, 1) }} MB)</label>
                    <input id="file" name="file" type="file" class="input" required>
                </div>
            </div>

            @error('file')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('title')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn-accent">Upload & scan</button>
            </div>
        </form>

        <div class="card">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 class="text-lg font-bold text-ink-900">Documents</h2>
                <form method="GET" action="{{ route('admin.documents.index') }}" class="flex items-center gap-2">
                    <input name="q" value="{{ request('q') }}" placeholder="Search…" class="input">
                    <button type="submit" class="btn-secondary">Search</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-200 text-xs uppercase tracking-wide text-ink-400">
                            <th class="py-2 pr-4">Title</th>
                            <th class="py-2 pr-4">File</th>
                            <th class="py-2 pr-4">Scan</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Uploaded</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($documents as $document)
                            <tr class="border-b border-ink-100">
                                <td class="py-2.5 pr-4 font-medium text-ink-800">{{ $document->title }}</td>
                                <td class="py-2.5 pr-4 text-xs text-ink-500">
                                    {{ $document->original_name }}<br>
                                    {{ round($document->size / 1024, 1) }} KB · {{ strtoupper(pathinfo($document->original_name, PATHINFO_EXTENSION)) }}
                                </td>
                                <td class="py-2.5 pr-4">
                                    @if ($document->is_quarantined)
                                        <span class="badge badge-red">Quarantined</span>
                                    @elseif ($document->scan_status === 'clean')
                                        <span class="badge badge-green">Clean</span>
                                    @elseif ($document->scan_status === 'error')
                                        <span class="badge badge-gray">Unscanned</span>
                                    @else
                                        <span class="badge badge-gray">{{ $document->scan_status }}</span>
                                    @endif
                                </td>
                                <td class="py-2.5 pr-4">
                                    <span class="badge {{ $document->status === 'rejected' ? 'badge-red' : 'badge-gray' }}">{{ \App\Models\Document::STATUSES[$document->status] ?? $document->status }}</span>
                                </td>
                                <td class="py-2.5 pr-4 text-xs text-ink-500">{{ $document->created_at->format('d M Y H:i') }}</td>
                                <td class="py-2.5">
                                    <div class="flex items-center justify-end gap-2">
                                        @unless ($document->is_quarantined)
                                            <a href="{{ route('admin.documents.download', $document) }}" class="btn-secondary px-3 py-1.5 text-xs">Download</a>
                                        @endunless
                                        <form method="POST" action="{{ route('admin.documents.destroy', $document) }}" onsubmit="return confirm('Delete this document permanently?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-danger px-3 py-1.5 text-xs">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-sm text-ink-400">No documents uploaded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $documents->links() }}</div>
        </div>
    </div>
</x-layouts.app>
