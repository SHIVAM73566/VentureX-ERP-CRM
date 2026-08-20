<x-layouts.app :title="'Import Data'" :breadcrumbs="[['label' => 'Administration'], ['label' => 'Import Data']]">

    <div class="mx-auto max-w-7xl space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-ink-900">Import Data</h1>
                <p class="mt-1 text-sm text-ink-500">Import customers, suppliers, products, and more from CSV or JSON files.</p>
            </div>
            <a href="{{ route('admin.imports.create') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New Import
            </a>
        </div>

        @if($imports->count() === 0)
            <div class="rounded-xl border border-ink-200 bg-white p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <h3 class="mt-4 text-lg font-medium text-ink-900">No imports yet</h3>
                <p class="mt-2 text-sm text-ink-500">Upload a CSV or JSON file to import your business data.</p>
                <a href="{{ route('admin.imports.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Start Import</a>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-ink-200 bg-white">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-ink-200">
                    <thead class="bg-ink-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Import</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Records</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-ink-500">Date</th>
                            <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-ink-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-200">
                        @foreach($imports as $import)
                        <tr class="hover:bg-ink-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-ink-900">{{ $import->file_name }}</div>
                                <div class="text-xs text-ink-500">by {{ $import->user->name ?? 'Unknown' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-indigo-100 px-2.5 py-0.5 text-xs font-medium text-indigo-800">{{ $import->getDestinationLabel() }}</span>
                            </td>
                            <td class="px-6 py-4 text-sm text-ink-600">
                                {{ $import->created_rows }} created
                                @if($import->updated_rows > 0) &middot; {{ $import->updated_rows }} updated @endif
                                @if($import->failed_rows > 0) &middot; <span class="text-red-600">{{ $import->failed_rows }} failed</span> @endif
                            </td>
                            <td class="px-6 py-4">
                                @php $statusColors = ['completed' => 'green', 'completed_with_errors' => 'yellow', 'processing' => 'blue', 'pending' => 'gray', 'failed' => 'red', 'uploaded' => 'blue']; @endphp
                                @php $color = $statusColors[$import->status] ?? 'gray'; @endphp
                                <span class="inline-flex items-center rounded-full bg-{{ $color }}-100 px-2.5 py-0.5 text-xs font-medium text-{{ $color }}-800">{{ ucfirst(str_replace('_', ' ', $import->status)) }}</span>
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
            </div>
            <div>{{ $imports->links() }}</div>
        @endif
    </div>

</x-layouts.app>
