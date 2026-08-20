<x-layouts.app title="AI Document Reader" :breadcrumbs="[['label' => 'AI'], ['label' => 'Document Reader']]">

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Analyze a Document</h2>
                <form method="POST" action="{{ route('ai.document-reader.analyze') }}" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4">
                    @csrf
                    <x-form.select label="Document *" name="document_id" :options="$documents->mapWithKeys(fn ($d) => [$d->id => ($d->original_name ?? ('#'.$d->id))])" required />
                    <x-form.input label="Focus (optional)" name="focus" placeholder="e.g. Extract payment terms and price clauses" />
                    <label class="flex items-start gap-2 rounded-lg border border-ink-200 p-3 text-sm text-ink-600">
                        <input type="checkbox" name="deep" value="1" class="mt-0.5">
                        <span>
                            <span class="font-semibold text-ink-800">Complex analysis</span>
                            <span class="block text-xs text-ink-400">Deeper reasoning for contracts and technical documents: obligations, payment terms, risks and inconsistencies. Only use when you need it.</span>
                        </span>
                    </label>
                    <button type="submit" class="btn-accent w-full" :disabled="submitting">
                        <template x-if="!submitting"><span>Analyze Document</span></template>
                        <template x-if="submitting"><span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Analyzing…</span></template>
                    </button>
                </form>
                <p class="mt-3 text-xs text-ink-400">Runs on {{ config('ai.provider') }} via {{ config('ai.model') }}. Analyses are saved to audit logs and run history.</p>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            @php $activeRunId = request()->integer('run'); @endphp
            @forelse ($runs as $run)
                <div class="card {{ $run->id === $activeRunId ? 'ring-2 ring-navy-500' : '' }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-ink-400">{{ $run->created_at?->format('d M Y H:i') }} • {{ $run->model ?? '—' }}</p>
                            <h3 class="mt-0.5 font-bold text-ink-900">{{ $run->input['document_id'] ?? '' ? 'Document analysis' : 'Analysis' }}</h3>
                        </div>
                        @if ($run->status === 'completed')
                            <span class="badge-green">Completed</span>
                        @elseif ($run->status === 'running')
                            <span class="badge-amber">Running</span>
                        @else
                            <span class="badge-red">Failed</span>
                        @endif
                    </div>

                    @if ($run->status === 'completed' && isset($run->output['content']))
                        <div class="mt-3 rounded-lg bg-ink-50 p-4">
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-ink-800">{{ $run->output['content'] }}</p>
                        </div>
                        <p class="mt-2 text-xs text-ink-400">{{ $run->prompt_tokens ?? 0 }} prompt tokens • {{ $run->completion_tokens ?? 0 }} completion tokens</p>
                    @elseif ($run->status === 'failed')
                        <p class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700">{{ $run->error['message'] ?? 'Analysis failed.' }}</p>
                    @else
                        <p class="mt-3 text-sm text-ink-400">Analysis in progress…</p>
                    @endif
                </div>
            @empty
                <div class="card"><p class="py-8 text-center text-ink-400">No document analyses yet. Select a document and run an analysis.</p></div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
