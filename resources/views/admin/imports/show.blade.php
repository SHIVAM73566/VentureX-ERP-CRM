<x-layouts.app :title="'Import Details'" :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => $import->file_name]]">

    <div class="mx-auto max-w-6xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('admin.imports.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to Imports</a>
                <h1 class="mt-2 text-2xl font-bold text-ink-900">{{ $import->file_name }}</h1>
                <p class="mt-1 text-sm text-ink-500">{{ $import->getDestinationLabel() }} &middot; {{ $import->created_at->diffForHumans() }}</p>
            </div>
            <div class="flex gap-3">
                @if($import->failed_rows > 0 || $import->skipped_rows > 0)
                <a href="{{ route('admin.imports.error-report', $import) }}" class="btn-secondary">Error Report</a>
                @endif
                <form action="{{ route('admin.imports.destroy', $import) }}" method="POST" onsubmit="return confirm('Delete this import?')">
                    @csrf @method('DELETE')
                    <button class="rounded-lg border border-red-300 px-3 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Delete</button>
                </form>
            </div>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-ink-200 bg-white p-4 text-center">
                <div class="text-2xl font-bold text-ink-900">{{ number_format($import->total_rows) }}</div>
                <div class="text-xs text-ink-500">Total Rows</div>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-center">
                <div class="text-2xl font-bold text-green-700">{{ number_format($import->created_rows) }}</div>
                <div class="text-xs text-green-600">Created</div>
            </div>
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-center">
                <div class="text-2xl font-bold text-yellow-700">{{ number_format($import->skipped_rows) }}</div>
                <div class="text-xs text-yellow-600">Skipped</div>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-center">
                <div class="text-2xl font-bold text-red-700">{{ number_format($import->failed_rows) }}</div>
                <div class="text-xs text-red-600">Failed</div>
            </div>
        </div>

        <div class="rounded-xl border border-ink-200 bg-white">
            <div class="border-b border-ink-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-ink-900">Row Details</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-ink-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-ink-500">Row</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-ink-500">Status</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-ink-500">Record ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-ink-500">Errors</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-ink-500">Raw Data</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach($rows as $row)
                        <tr class="hover:bg-ink-50">
                            <td class="px-4 py-2 text-xs text-ink-500">{{ $row->row_number }}</td>
                            <td class="px-4 py-2">
                                @php $colors = ['created' => 'green', 'updated' => 'blue', 'skipped' => 'yellow', 'failed' => 'red']; @endphp
                                <span class="rounded-full bg-{{ $colors[$row->status] ?? 'gray' }}-100 px-2 py-0.5 text-xs font-medium text-{{ $colors[$row->status] ?? 'gray' }}-800">{{ ucfirst($row->status) }}</span>
                            </td>
                            <td class="px-4 py-2 text-xs text-ink-500">{{ $row->imported_record_id ?? '&mdash;' }}</td>
                            <td class="px-4 py-2 text-xs text-red-600">{{ $row->errors ?? '&mdash;' }}</td>
                            <td class="px-4 py-2 text-xs text-ink-500 max-w-xs truncate">{{ is_array($row->raw_data) ? json_encode($row->raw_data) : $row->raw_data }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-ink-200 px-6 py-3">
                {{ $rows->links() }}
            </div>
        </div>
    </div>

</x-layouts.app>
