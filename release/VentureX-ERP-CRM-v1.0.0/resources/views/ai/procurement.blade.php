<x-layouts.app title="Procurement AI" :breadcrumbs="[['label' => 'AI'], ['label' => 'Procurement Intelligence']]">

    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 300)">
        <div x-show="loading" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @for($i = 0; $i < 4; $i++)
                <x-ui.skeleton-stat />
            @endfor
        </div>
        <div x-show="!loading" x-cloak class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <x-dashboard.stat-card label="Supplier Offers" :value="$summary['offers']" icon="document" color="blue" />
            <x-dashboard.stat-card label="Open Requisitions" :value="$summary['open_requisitions']" icon="alert" color="amber" />
            <x-dashboard.stat-card label="Suppliers" :value="$summary['suppliers']" icon="users" color="green" />
            <x-dashboard.stat-card label="Red-Flag Offers" :value="$summary['red_offers']" icon="flag" color="red" />
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-3 mt-6">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Ask Procurement Intelligence</h2>
            <form method="POST" action="{{ route('ai.procurement.analyze') }}" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4">
                @csrf
                <x-form.textarea label="Question *" name="question" rows="5" required placeholder="e.g. Compare the best price per MT for HRC grade A36 among the latest offers, and flag any high-risk suppliers." />
                <button type="submit" class="btn-accent w-full" :disabled="submitting">
                    <template x-if="!submitting"><span>Analyze</span></template>
                    <template x-if="submitting"><span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Analyzing…</span></template>
                </button>
            </form>
            <div class="mt-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wide text-ink-400">Deep analysis</h3>
                    <p class="mt-1 text-xs text-ink-500">Specialist best-value supplier comparison with risks, negotiation opportunities and alternatives.</p>
                </div>
                <x-ai.action label="Deep Procurement Analysis" :url="route('ai.deep.procurement')"
                    intro="Comparing the latest supplier offers across materials: best-value supplier, reasons, risks, negotiation opportunities and alternatives — for your review. A purchase order is only created after you review and confirm the recommendation." />
            </div>
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                Recommendations never create a purchase order. Review the recommendation, then confirm before creating a PO.
            </div>
            <div class="mt-4">
                <h3 class="text-xs font-bold uppercase tracking-wide text-ink-400">Example questions</h3>
                <ul class="mt-2 space-y-1 text-xs text-ink-500">
                    <li>• "Which supplier has the best offer for TMT bars?"</li>
                    <li>• "Summarize open requisitions and suggested suppliers."</li>
                    <li>• "Any red-flag offers with poor quality status?"</li>
                </ul>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            @php $activeRunId = request()->integer('run'); @endphp
            @forelse ($runs as $run)
                <div class="card {{ $run->id === $activeRunId ? 'ring-2 ring-navy-500' : '' }}">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-ink-400">{{ $run->created_at?->format('d M Y H:i') }} • {{ $run->model ?? '—' }}</p>
                            <h3 class="mt-0.5 font-bold text-ink-900">Q: {{ $run->input['question'] ?? 'Procurement analysis' }}</h3>
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
                <div class="card"><p class="py-8 text-center text-ink-400">No procurement analyses yet. Ask your first question.</p></div>
            @endforelse
        </div>
    </div>
</x-layouts.app>
