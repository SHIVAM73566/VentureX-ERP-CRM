<x-layouts.app title="Business Copilot" :breadcrumbs="[['label' => 'AI Center'], ['label' => 'Business Copilot']]">

    <div x-data="copilot()" x-init="init()" class="grid gap-6 md:grid-cols-4 lg:grid-cols-4">
        <div class="lg:col-span-1 space-y-6">
            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400 dark:text-ink-500">Try asking</h3>
                <div class="space-y-2">
                    <template x-for="q in suggestions" :key="q">
                        <button type="button" @click="ask(q)"
                            class="block w-full rounded-lg border border-ink-200 px-3 py-2 text-left text-sm text-ink-600 transition hover:border-navy-400 hover:bg-navy-50 dark:border-ink-700 dark:text-ink-400 dark:hover:border-navy-500 dark:hover:bg-navy-500/10">
                            <span x-text="q"></span>
                        </button>
                    </template>
                </div>
            </div>

            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400 dark:text-ink-500">How it works</h3>
                <ol class="space-y-2 text-sm text-ink-600 dark:text-ink-400">
                    <li>1. Simple questions are answered instantly from your ERP data.</li>
                    <li>2. Deeper questions are analysed by a secure backend AI.</li>
                    <li>3. Answers are labelled [FACT], [CALCULATION], [ASSUMPTION] and [RECOMMENDATION].</li>
                    <li>4. Every answer can be reviewed before acting.</li>
                </ol>
                <p class="mt-3 text-xs text-ink-400 dark:text-ink-500">AI never changes your data — it only explains and recommends.</p>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="card flex min-h-0 flex-1 lg:h-[calc(100vh-16rem)] flex-col">
                <div class="mb-4 border-b border-ink-100 pb-3 dark:border-ink-800">
                    <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Business Copilot</h2>
                    <p class="text-sm text-ink-400 dark:text-ink-500">Ask about receivables, overdue invoices, stock levels, suppliers, or your overall business health.</p>
                </div>

                <div id="copilot-messages" class="flex-1 space-y-4 overflow-y-auto pr-1">
                    <template x-if="messages.length === 0">
                        <div class="flex h-full items-center justify-center">
                            <div class="text-center">
                                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-navy-600 text-white">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <p class="text-lg font-semibold text-ink-700 dark:text-ink-200">What would you like to know?</p>
                                <p class="mt-1 text-sm text-ink-400 dark:text-ink-500">Try one of the suggestions on the left, or type your own question.</p>
                            </div>
                        </div>
                    </template>

                    <template x-for="(m, i) in messages" :key="i">
                        <div class="flex" :class="m.role === 'user' ? 'justify-end' : 'justify-start'">
                            <div class="max-w-[85%] rounded-2xl px-4 py-2 text-sm"
                                :class="m.role === 'user' ? 'rounded-br-sm bg-navy-600 text-white' : (m.mode === 'local' ? 'rounded-bl-sm bg-emerald-50 text-emerald-900 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:border-emerald-800/50' : 'rounded-bl-sm bg-ink-100 text-ink-800 dark:bg-ink-800 dark:text-ink-200')">
                                <p class="whitespace-pre-wrap" x-text="m.content"></p>
                                <p x-show="m.role === 'assistant'" class="mt-2 text-xs text-ink-400 dark:text-ink-500">
                                    <span x-show="m.mode === 'local'">Answered instantly from ERP data</span>
                                    <span x-show="m.mode === 'ai'">AI analysis <span x-show="m.cached">· cached</span></span>
                                </p>
                            </div>
                        </div>
                    </template>

                    <div x-show="loading" class="flex justify-start">
                        <div class="max-w-[85%] rounded-2xl rounded-bl-sm bg-ink-100 px-4 py-2 text-sm text-ink-400 flex items-center gap-2 dark:bg-ink-800 dark:text-ink-500">
                            <svg class="animate-spin h-4 w-4 text-ink-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Analysing…
                        </div>
                    </div>
                </div>

                <form class="mt-4 flex items-end gap-2" @submit.prevent="ask(question)">
                    <textarea name="question" rows="1" placeholder="Type your business question…" class="input flex-1 resize-none" x-model="question"></textarea>
                    <button type="submit" class="btn-accent !px-4">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function copilot() {
            return {
                question: '',
                loading: false,
                messages: [],
                suggestions: [
                    'What should I do today?',
                    'Generate an executive business review',
                    'Which invoices are overdue and what is the total outstanding amount?',
                    'Which products are below their reorder level?',
                    'Are there any suppliers we should review before the next purchase order?',
                    'Which customers owe us the most money right now?'
                ],
                init() {
                    const el = document.getElementById('copilot-messages');
                    if (el) {
                        const observer = new MutationObserver(() => { el.scrollTop = el.scrollHeight; });
                        observer.observe(el, { childList: true, subtree: true });
                    }
                },
                async ask(text) {
                    const question = (text || this.question).trim();
                    if (!question || this.loading) return;

                    this.messages.push({ role: 'user', content: question });
                    this.question = '';
                    this.loading = true;

                    const payload = new FormData();
                    payload.append('question', question);

                    try {
                        const res = await fetch(@json(route('ai.copilot.ask')), {
                            method: 'POST',
                            body: payload,
                            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok) {
                            this.messages.push({ role: 'assistant', content: data.content, mode: data.mode, cached: data.cached });
                        } else {
                            this.messages.push({ role: 'assistant', content: data.error || 'Something went wrong. Please try again.', mode: 'error' });
                        }
                    } catch (err) {
                        this.messages.push({ role: 'assistant', content: 'Could not reach the server. Please try again.', mode: 'error' });
                    } finally {
                        this.loading = false;
                    }
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
