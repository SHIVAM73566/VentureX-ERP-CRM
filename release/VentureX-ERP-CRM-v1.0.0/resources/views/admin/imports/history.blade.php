<x-layouts.app :title="'Import History'" :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Import History']]">

    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-ink-900">Import History</h1>
            <p class="mt-1 text-sm text-ink-500">View all past imports and their status.</p>
        </div>

        @if($imports->count() === 0)
            <div class="rounded-xl border border-ink-200 bg-white p-12 text-center">
                <p class="text-sm text-ink-500">No imports have been run yet.</p>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-ink-200 bg-white">
                <table class="min-w-full divide-y divide-ink-200">
                    <thead class="bg-ink-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Created</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Updated</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Skipped</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Failed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-200">
                        @foreach($imports as $import)
                        <tr class="hover:bg-ink-50">
                            <td class="px-6 py-4 text-sm font-medium text-ink-900">{{ $import->file_name }}</td>
                            <td class="px-6 py-4 text-sm text-ink-600">{{ $import->getDestinationLabel() }}</td>
                            <td class="px-6 py-4 text-sm text-ink-600">{{ $import->created_rows }}</td>
                            <td class="px-6 py-4 text-sm text-ink-600">{{ $import->updated_rows }}</td>
                            <td class="px-6 py-4 text-sm text-ink-600">{{ $import->skipped_rows }}</td>
                            <td class="px-6 py-4 text-sm text-ink-600">{{ $import->failed_rows }}</td>
                            <td class="px-6 py-4">
                                @php $colors = ['completed' => 'green', 'completed_with_errors' => 'yellow', 'processing' => 'blue', 'pending' => 'gray', 'failed' => 'red', 'uploaded' => 'blue']; @endphp
                                <span class="rounded-full bg-{{ $colors[$import->status] ?? 'gray' }}-100 px-2.5 py-0.5 text-xs font-medium text-{{ $colors[$import->status] ?? 'gray' }}-800">{{ ucfirst(str_replace('_', ' ', $import->status)) }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-ink-500">{{ $import->created_at->diffForHumans() }}</td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.imports.show', $import) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div>{{ $imports->links() }}</div>
        @endif
    </div>

</x-layouts.app>
