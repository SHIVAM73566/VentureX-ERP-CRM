<x-layouts.app :title="'Import Preview'" :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Preview']]">

    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-ink-500">
                <span>Upload</span><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Map Columns</span><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-indigo-600">Preview</span><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Import</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold text-ink-900">Import Preview</h1>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-ink-200 bg-white p-4 text-center">
                <div class="text-2xl font-bold text-ink-900">{{ number_format($stats['total']) }}</div>
                <div class="text-xs text-ink-500">Total Rows</div>
            </div>
            <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-center">
                <div class="text-2xl font-bold text-green-700">{{ number_format($stats['total'] - $stats['errors'] - $stats['duplicates']) }}</div>
                <div class="text-xs text-green-600">Valid</div>
            </div>
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 text-center">
                <div class="text-2xl font-bold text-yellow-700">{{ number_format($stats['duplicates']) }}</div>
                <div class="text-xs text-yellow-600">Duplicates</div>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-center">
                <div class="text-2xl font-bold text-red-700">{{ number_format($stats['errors']) }}</div>
                <div class="text-xs text-red-600">Errors</div>
            </div>
        </div>

        @if($stats['errors'] > 0)
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <h3 class="text-sm font-semibold text-red-800">Validation Errors Found</h3>
            <div class="mt-2 max-h-40 overflow-y-auto text-xs text-red-700">
                @foreach($validation['errors'] as $rowIndex => $errs)
                    @if(!empty($errs))
                        <div class="mb-1"><strong>Row {{ $rowIndex + 1 }}:</strong> {{ implode(', ', $errs) }}</div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <div class="rounded-xl border border-ink-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold text-ink-900">Data Preview (first 50 rows)</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-ink-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-ink-500">#</th>
                            @foreach($mappings as $m)
                                @if(!empty($m['field']))
                                <th class="px-3 py-2 text-left text-xs font-medium text-ink-500">{{ ucfirst(str_replace('_', ' ', $m['field'])) }}</th>
                                @endif
                            @endforeach
                            <th class="px-3 py-2 text-left text-xs font-medium text-ink-500">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-ink-100">
                        @foreach($rows as $i => $row)
                        <tr class="hover:bg-ink-50">
                            <td class="px-3 py-2 text-xs text-ink-500">{{ $i + 1 }}</td>
                            @foreach($mappings as $m)
                                @if(!empty($m['field']))
                                <td class="px-3 py-2 text-xs text-ink-700">{{ $row[$m['column']] ?? '' }}</td>
                                @endif
                            @endforeach
                            <td class="px-3 py-2">
                                @php $rowErrors = $validation['errors'][$i] ?? []; @endphp
                                @if(!empty($rowErrors))
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800">Error</span>
                                @elseif(!empty($duplicates[$i]))
                                    <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Duplicate</span>
                                @else
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">Valid</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-xl border border-ink-200 bg-white p-6">
            <h3 class="mb-3 text-sm font-semibold text-ink-900">Duplicate Strategy</h3>
            <p class="mb-3 text-xs text-ink-500">Choose how to handle {{ $stats['duplicates'] }} duplicate records:</p>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="duplicate_strategy" value="skip" checked class="text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-ink-700">Skip duplicates</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="duplicate_strategy" value="update" class="text-indigo-600 focus:ring-indigo-500">
                    <span class="text-sm text-ink-700">Update existing records</span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="{{ route('admin.imports.show', $import) }}" class="btn-secondary">Back to Mapping</a>
            <form action="{{ route('admin.imports.execute') }}" method="POST" onsubmit="return confirm('Import {{ number_format($stats['total']) }} records into {{ $import->getDestinationLabel() }}?')">
                @csrf
                <input type="hidden" name="import_id" value="{{ $import->id }}">
                <input type="hidden" name="duplicate_strategy" id="dup-strategy" value="skip">
                <button type="submit" class="rounded-lg bg-green-600 px-6 py-3 text-sm font-medium text-white hover:bg-green-700">
                    Confirm &amp; Import {{ number_format($stats['total']) }} Records
                </button>
            </form>
        </div>
    </div>

    <script>
    document.querySelectorAll('input[name="duplicate_strategy"]').forEach(r => {
        r.addEventListener('change', () => document.getElementById('dup-strategy').value = r.value);
    });
    </script>

</x-layouts.app>
