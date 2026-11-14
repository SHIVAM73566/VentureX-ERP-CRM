<x-layouts.app title="Enterprise RAG" :breadcrumbs="[['label' => 'AI Center'], ['label' => 'Enterprise RAG']]">

    @php
        $pipelineData = $pipelines->map(fn ($pipeline) => [
            'key' => $pipeline->key,
            'name' => $pipeline->name,
            'domain' => $pipeline->domain,
            'description' => $pipeline->description,
            'confidence' => (float) $pipeline->confidence,
            'documents' => (int) $pipeline->document_count,
            'last_indexed_at' => $pipeline->last_indexed_at?->toIso8601String(),
        ])->values();
    @endphp

    <div x-data="ragWorkspace({
            pipelines: @js($pipelineData),
            initial: @js($selected?->key),
            domains: @js($domains),
            endpoints: {
                query: @js(route('ai.rag.query')),
                sync: @js(route('ai.rag.sync')),
            },
        })" x-init="init()" class="space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Enterprise RAG workspace</h2>
                <p class="text-sm text-ink-500 dark:text-ink-400">84 focused pipelines across 7 domains. Answers come from your own records.</p>
            </div>
            <a href="{{ route('ai.rag.tour') }}" class="btn-secondary">Open the pipeline tour →</a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-400 dark:text-ink-500">Pipelines</p>
                <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-ink-50">{{ $stats['pipelines'] }}</p>
                <p class="text-xs text-ink-400 dark:text-ink-500">{{ $stats['active_pipelines'] }} active · 6 domains</p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-400 dark:text-ink-500">Indexed Records</p>
                <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-ink-50">{{ number_format($stats['documents']) }}</p>
                <p class="text-xs text-ink-400 dark:text-ink-500">Vector documents in the local store</p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-400 dark:text-ink-500">Avg Confidence</p>
                <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-ink-50">{{ number_format($stats['avg_confidence'] * 100, 1) }}%</p>
                <p class="text-xs text-ink-400 dark:text-ink-500">Continuously tuned by self-training</p>
            </div>
            <div class="card p-5">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-400 dark:text-ink-500">Last Training</p>
                <p class="mt-1 text-2xl font-bold text-ink-900 dark:text-ink-50">{{ $stats['last_trained_at'] ? \Illuminate\Support\Carbon::parse($stats['last_trained_at'])->diffForHumans() : 'Not yet' }}</p>
                <p class="text-xs text-ink-400 dark:text-ink-500">Nightly self-training pipeline</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-12">
            {{-- Pipeline browser --}}
            <div class="card flex min-w-0 flex-col xl:col-span-4">
                <div class="card-header flex-col !items-stretch gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-ink-400 dark:text-ink-500">Pipelines</h2>
                        <button type="button" class="btn-secondary !px-2.5 !py-1.5 text-xs" @click="indexAll()" :disabled="syncing">
                            <span x-show="!syncing">Index all</span>
                            <span x-show="syncing">Indexing…</span>
                        </button>
                    </div>
                    <input type="search" x-model="search" placeholder="Search pipelines…" class="input w-full" />
                </div>

                <div class="flex flex-wrap gap-1.5 border-b border-ink-100 px-4 py-3 dark:border-ink-800">
                    <button type="button" @click="domain = 'all'"
                        class="badge cursor-pointer border"
                        :class="domain === 'all' ? 'border-navy-500 bg-navy-50 text-navy-700 dark:bg-navy-500/10 dark:text-navy-300' : 'border-ink-200 text-ink-500 dark:border-ink-700 dark:text-ink-400'">
                        All
                    </button>
                    <template x-for="(info, key) in domains" :key="key">
                        <button type="button" @click="domain = key"
                            class="badge cursor-pointer border"
                            :class="domain === key ? 'border-navy-500 bg-navy-50 text-navy-700 dark:bg-navy-500/10 dark:text-navy-300' : 'border-ink-200 text-ink-500 dark:border-ink-700 dark:text-ink-400'">
                            <span x-text="shortLabel(info.label)"></span>
                            <span class="ml-1 opacity-60" x-text="info.pipelines"></span>
                        </button>
                    </template>
                </div>

                <div class="max-h-[28rem] min-w-0 overflow-y-auto overscroll-contain p-2">
                    <template x-for="pipeline in filtered" :key="pipeline.key">
                        <button type="button" @click="select(pipeline)"
                            class="mb-1 block w-full rounded-lg px-3 py-2 text-left transition"
                            :class="selected === pipeline.key
                                ? 'bg-navy-50 text-navy-800 ring-1 ring-navy-300 dark:bg-navy-500/10 dark:text-navy-200 dark:ring-navy-700'
                                : 'text-ink-600 hover:bg-ink-50 dark:text-ink-300 dark:hover:bg-ink-800'">
                            <span class="flex items-center justify-between gap-2">
                                <span class="min-w-0 truncate text-sm font-medium" x-text="pipeline.name"></span>
                                <span class="shrink-0 text-[11px] text-ink-400" x-text="pipeline.documents"></span>
                            </span>
                            <span class="mt-0.5 block truncate text-xs text-ink-400 dark:text-ink-500" x-text="pipeline.description"></span>
                        </button>
                    </template>
                    <p x-show="filtered.length === 0" class="px-3 py-6 text-center text-sm text-ink-400">No pipelines match this filter.</p>
                </div>
            </div>

            {{-- Query workspace --}}
            <div class="min-w-0 space-y-6 xl:col-span-8">
                <div class="card min-w-0">
                    <div class="card-header">
                        <div class="min-w-0">
                            <h2 class="truncate text-lg font-bold text-ink-900 dark:text-ink-50" x-text="current?.name ?? 'Select a pipeline'"></h2>
                            <p class="truncate text-sm text-ink-400 dark:text-ink-500" x-text="current?.description ?? 'Choose a pipeline to start a retrieval query.'"></p>
                        </div>
                        <button type="button" class="btn-secondary shrink-0 !px-2.5 !py-1.5 text-xs" @click="indexSelected()" :disabled="syncing || !current">
                            <span x-show="!syncing">Index now</span>
                            <span x-show="syncing">Working…</span>
                        </button>
                    </div>

                    <div class="p-5">
                        <form @submit.prevent="ask()" class="space-y-3">
                            <textarea x-model="question" rows="3" class="input w-full resize-y"
                                placeholder="Ask a question about this pipeline, e.g. which customers are most at risk of churn?"></textarea>

                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="flex flex-wrap items-center gap-4 text-sm text-ink-500 dark:text-ink-400">
                                    <label class="flex items-center gap-2">
                                        Results
                                        <select x-model.number="limit" class="input !w-20 !py-1">
                                            <template x-for="n in [5, 8, 12, 20]" :key="n">
                                                <option :value="n" x-text="n"></option>
                                            </template>
                                        </select>
                                    </label>
                                    <label class="flex items-center gap-2">
                                        <input type="checkbox" x-model="useAi" class="rounded border-ink-300 text-navy-600" />
                                        Use AI narrative
                                    </label>
                                </div>
                                <button type="submit" class="btn-accent" :disabled="loading || !current">
                                    <span x-show="!loading">Run query</span>
                                    <span x-show="loading">Retrieving…</span>
                                </button>
                            </div>
                        </form>

                        <div x-show="error" x-cloak class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-300" x-text="error"></div>
                        <div x-show="notice" x-cloak class="mt-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-300" x-text="notice"></div>
                    </div>
                </div>

                <div class="card min-w-0" x-show="answer" x-cloak>
                    <div class="card-header">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-ink-400 dark:text-ink-500">Answer</h2>
                        <div class="flex items-center gap-2">
                            <span class="badge-gray" x-text="mode === 'ai' ? 'AI narrative' : 'Local retrieval'"></span>
                            <span class="badge-blue" x-text="latency + ' ms'"></span>
                        </div>
                    </div>
                    <div class="p-5">
                        <pre class="whitespace-pre-wrap break-words font-sans text-sm leading-relaxed text-ink-700 dark:text-ink-200" x-text="answer"></pre>
                    </div>
                </div>

                <div class="card min-w-0" x-show="sources.length > 0" x-cloak>
                    <div class="card-header">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-ink-400 dark:text-ink-500">Retrieved records</h2>
                        <span class="text-xs text-ink-400" x-text="sources.length + ' sources'"></span>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th class="w-12">#</th>
                                    <th>Record</th>
                                    <th>Type</th>
                                    <th class="text-right">Match</th>
                                    <th>Excerpt</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="(source, i) in sources" :key="source.id">
                                    <tr>
                                        <td class="text-ink-400" x-text="i + 1"></td>
                                        <td class="max-w-[14rem] truncate font-medium" :title="source.title" x-text="source.title"></td>
                                        <td><span class="badge-gray" x-text="source.source_type"></span></td>
                                        <td class="text-right font-semibold tabular-nums" x-text="(source.score * 100).toFixed(1) + '%'"></td>
                                        <td class="max-w-[22rem] truncate text-xs text-ink-500 dark:text-ink-400" :title="source.snippet" x-text="source.snippet"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="card min-w-0">
            <div class="card-header">
                <h2 class="text-sm font-bold uppercase tracking-wide text-ink-400 dark:text-ink-500">Recent queries</h2>
                <span class="text-xs text-ink-400">{{ $recentQueries->count() }} shown</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Pipeline</th>
                            <th>Query</th>
                            <th>Mode</th>
                            <th class="text-right">Top score</th>
                            <th class="text-right">Results</th>
                            <th class="text-right">Latency</th>
                            <th>When</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentQueries as $log)
                            <tr>
                                <td class="max-w-[14rem] truncate font-medium">{{ $log->pipeline?->name ?? '—' }}</td>
                                <td class="max-w-[22rem] truncate" title="{{ $log->query }}">{{ $log->query }}</td>
                                <td><span class="badge-{{ $log->mode === 'ai' ? 'violet' : 'green' }}">{{ $log->mode }}</span></td>
                                <td class="text-right tabular-nums">{{ number_format((float) $log->top_score * 100, 1) }}%</td>
                                <td class="text-right tabular-nums">{{ $log->result_count }}</td>
                                <td class="text-right tabular-nums">{{ $log->latency_ms }} ms</td>
                                <td class="whitespace-nowrap text-ink-500">{{ $log->created_at?->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="py-8 text-center text-ink-400 dark:text-ink-500">No queries yet. Run one above to see retrieval analytics.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function ragWorkspace(config) {
            return {
                pipelines: config.pipelines || [],
                domains: config.domains || {},
                endpoints: config.endpoints,
                selected: config.initial,
                domain: 'all',
                search: '',
                question: '',
                limit: 8,
                useAi: false,
                loading: false,
                syncing: false,
                answer: '',
                sources: [],
                mode: 'local',
                latency: 0,
                error: '',
                notice: '',

                init() {
                    if (!this.selected && this.pipelines.length) {
                        this.selected = this.pipelines[0].key;
                    }
                },

                get current() {
                    return this.pipelines.find(p => p.key === this.selected) || null;
                },

                get filtered() {
                    const term = this.search.trim().toLowerCase();
                    return this.pipelines.filter(p => {
                        const domainMatch = this.domain === 'all' || p.domain === this.domain;
                        const searchMatch = !term
                            || p.name.toLowerCase().includes(term)
                            || (p.description || '').toLowerCase().includes(term);
                        return domainMatch && searchMatch;
                    });
                },

                shortLabel(label) {
                    return (label || '').split(' ')[0];
                },

                select(pipeline) {
                    this.selected = pipeline.key;
                    this.answer = '';
                    this.sources = [];
                    this.error = '';
                    this.notice = '';
                },

                headers() {
                    return {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    };
                },

                async ask() {
                    const question = this.question.trim();
                    if (!question || !this.current || this.loading) return;

                    this.loading = true;
                    this.error = '';
                    this.notice = '';

                    try {
                        const res = await fetch(this.endpoints.query, {
                            method: 'POST',
                            headers: this.headers(),
                            body: JSON.stringify({
                                pipeline: this.selected,
                                question,
                                limit: this.limit,
                                use_ai: this.useAi,
                            }),
                        });
                        const data = await res.json().catch(() => ({}));

                        if (!res.ok || data.success === false) {
                            this.error = data.message || 'The query could not be completed.';
                            return;
                        }

                        this.answer = data.answer || '';
                        this.sources = data.sources || [];
                        this.mode = data.mode || 'local';
                        this.latency = data.latency_ms || 0;
                    } catch (e) {
                        this.error = 'Could not reach the server. Please try again.';
                    } finally {
                        this.loading = false;
                    }
                },

                async indexSelected() {
                    if (!this.current || this.syncing) return;
                    await this.sync(this.current.key);
                },

                async indexAll() {
                    if (this.syncing) return;
                    await this.sync(null);
                },

                async sync(pipeline) {
                    this.syncing = true;
                    this.error = '';
                    this.notice = '';

                    try {
                        const res = await fetch(this.endpoints.sync, {
                            method: 'POST',
                            headers: this.headers(),
                            body: JSON.stringify(pipeline ? { pipeline } : {}),
                        });
                        const data = await res.json().catch(() => ({}));

                        if (!res.ok || data.success === false) {
                            this.error = data.message || 'Indexing failed.';
                            return;
                        }

                        this.notice = data.message || 'Indexing complete.';
                        if (pipeline && this.current) {
                            this.current.documents = Math.max(this.current.documents, 0);
                        }
                    } catch (e) {
                        this.error = 'Could not reach the server. Please try again.';
                    } finally {
                        this.syncing = false;
                    }
                },
            };
        }
    </script>
    @endpush
</x-layouts.app>
