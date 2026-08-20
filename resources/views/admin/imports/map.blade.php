<x-layouts.app :title="'Map Columns'" :breadcrumbs="[['label' => 'Administration', 'url' => route('admin.imports.index')], ['label' => 'Map Columns']]">

    <div class="mx-auto max-w-6xl space-y-6">
        <div>
            <div class="flex items-center gap-2 text-sm text-ink-500">
                <span>Upload</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="font-medium text-indigo-600">Map Columns</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Preview</span>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Import</span>
            </div>
            <h1 class="mt-2 text-2xl font-bold text-ink-900">Map Your Columns</h1>
            <p class="mt-1 text-sm text-ink-500">Match your file columns to the correct ERP fields.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="lg:col-span-2 space-y-6">
                <form action="{{ route('admin.imports.mapping.save') }}" method="POST">
                    @csrf
                    <input type="hidden" name="import_id" value="{{ $import->id }}">

                    <div class="rounded-xl border border-ink-200 bg-white p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-ink-900">Column Mapping</h2>
                            <span class="text-xs text-ink-500">{{ count($columns) }} columns detected</span>
                        </div>

                        <div class="space-y-3">
                            @foreach($mappings as $i => $mapping)
                            <div class="flex items-center gap-3 rounded-lg border border-ink-200 p-3">
                                <div class="flex-1">
                                    <div class="text-sm font-medium text-ink-900">{{ $mapping['column'] }}</div>
                                    <div class="text-xs text-ink-500">Sample: {{ $preview[0][$mapping['column']] ?? '&mdash;' }}</div>
                                </div>
                                <svg class="h-5 w-5 flex-shrink-0 text-ink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                <select name="mappings[{{ $i }}][field]" class="w-48 rounded-lg border border-ink-300 bg-white px-3 py-2 text-sm">
                                    <option value="">&mdash; Ignore &mdash;</option>
                                    @foreach($fieldLabels as $field => $label)
                                        <option value="{{ $field }}" {{ ($mapping['field'] ?? '') === $field ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="mappings[{{ $i }}][column]" value="{{ $mapping['column'] }}">
                                @if(($mapping['confidence'] ?? 0) >= 70)
                                    <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800">{{ $mapping['confidence'] }}%</span>
                                @else
                                    <span class="rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-800">Review</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-xl border border-ink-200 bg-white p-6">
                        <h3 class="mb-3 text-sm font-medium text-ink-700">Import Settings</h3>
                        <label class="mb-1 block text-xs text-ink-500">Destination Module</label>
                        <select name="destination" class="input">
                            @foreach($allDestinations as $key => $label)
                                <option value="{{ $key }}" {{ $key === $import->destination ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="btn-primary">Continue to Preview</button>
                    </div>
                </form>
            </div>

            <div class="space-y-6">
                <div class="rounded-xl border border-ink-200 bg-white p-6">
                    <h3 class="mb-3 text-sm font-semibold text-ink-900">Auto-Detection</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-ink-500">Suggested module:</span>
                            <span class="font-medium text-ink-900">{{ ucfirst($detection['destination']) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-ink-500">Confidence:</span>
                            <span class="font-medium text-green-600">{{ $detection['confidence'] }}%</span>
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-ink-200 bg-white p-6">
                    <h3 class="mb-3 text-sm font-semibold text-ink-900">File Preview</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-xs">
                            <thead>
                                <tr>
                                    @foreach($columns as $col)
                                        <th class="whitespace-nowrap px-2 py-1 text-left font-medium text-ink-500">{{ $col }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($preview as $row)
                                <tr class="border-t border-ink-100">
                                    @foreach($columns as $col)
                                        <td class="whitespace-nowrap px-2 py-1 text-ink-600">{{ $row[$col] ?? '' }}</td>
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-2 text-xs text-ink-400">Showing first {{ count($preview) }} of {{ $import->total_rows }} rows</p>
                </div>
            </div>
        </div>
    </div>

</x-layouts.app>
