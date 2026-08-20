@props([
    'label' => 'AI analysis',
    'url' => '',
    'payload' => [],
    'intro' => 'AI will analyse the current record. Everything is generated for your review only.',
])

<div x-data="aiAction()">
    <button type="button" @click="run(@js($url), @js($payload))" :disabled="busy"
        class="btn-secondary" :class="busy ? 'opacity-60' : ''">
        <span x-show="!busy">
            <svg class="mr-1.5 inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            {{ $label }}
        </span>
        <span x-show="busy">Working…</span>
    </button>

    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4"
        @keydown.escape.window="close()">
        <div class="w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-xl" style="max-height: 80vh">
            <div class="flex items-center justify-between border-b border-ink-100 px-5 py-3">
                <h3 class="text-sm font-bold text-ink-800">{{ $label }}</h3>
                <button type="button" @click="close()" class="text-ink-400 transition hover:text-ink-600" aria-label="Close">✕</button>
            </div>
            <div class="p-5">
                <p x-show="busy" class="text-sm text-ink-500">AI is analysing…</p>
                <p x-show="error && !busy" class="mb-3 rounded-lg bg-red-50 p-3 text-sm text-red-700" x-text="error"></p>
                <p x-show="!result && !busy && !error" class="text-sm text-ink-500">{{ $intro }}</p>
                <div x-show="result" class="whitespace-pre-wrap rounded-lg bg-ink-50 p-4 text-sm text-ink-800" x-text="result"></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    if (!window.aiAction) {
        window.aiAction = function () {
            return {
                busy: false,
                open: false,
                result: '',
                error: '',
                async run(url, payload) {
                    this.busy = true;
                    this.open = true;
                    this.error = '';
                    this.result = '';

                    const body = new FormData();
                    Object.entries(payload).forEach(([k, v]) => body.append(k, v));

                    try {
                        const res = await fetch(url, {
                            method: 'POST',
                            body,
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok) {
                            this.result = data.content;
                        } else {
                            this.error = data.error || 'AI is not configured yet. Set RAPIDAPI_KEY to enable AI analysis.';
                        }
                    } catch (err) {
                        this.error = 'Could not reach the server. Please try again.';
                    } finally {
                        this.busy = false;
                    }
                },
                close() {
                    this.open = false;
                }
            };
        }
    }
</script>
@endpush
