<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'AI Support Assistant','breadcrumbs' => [['label' => 'AI Center'], ['label' => 'Support Assistant']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'AI Support Assistant','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'AI Center'], ['label' => 'Support Assistant']])]); ?>

<div x-data="supportAssistant()" x-init="init()" class="grid gap-6 lg:grid-cols-[280px_1fr]">

    
    <div class="hidden lg:block space-y-5">
        <div class="card !p-4">
            <div class="flex items-center gap-3 mb-4">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-navy-600 to-accent-600 text-white shadow-lg shadow-navy-500/25">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-ink-900 dark:text-ink-50">Help Topics</h3>
                    <p class="text-[11px] text-ink-400">Browse common topics</p>
                </div>
            </div>
            <div class="space-y-1">
                <template x-for="(topic, i) in helpTopics" :key="i">
                    <button type="button" @click="askTopic(topic.question)"
                        class="w-full text-left rounded-lg px-3 py-2 text-[13px] text-ink-600 transition hover:bg-navy-50 hover:text-navy-700 dark:text-ink-400 dark:hover:bg-navy-500/10 dark:hover:text-navy-300 flex items-center gap-2.5">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-ink-100 dark:bg-ink-800" x-html="topic.icon"></span>
                        <span class="truncate" x-text="topic.label"></span>
                    </button>
                </template>
            </div>
        </div>

        <div class="card !p-4">
            <h3 class="mb-3 text-xs font-bold uppercase tracking-wide text-ink-400 dark:text-ink-500">How it works</h3>
            <ol class="space-y-2 text-[12px] text-ink-500 dark:text-ink-400">
                <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-navy-100 text-[9px] font-bold text-navy-600 dark:bg-navy-500/20 dark:text-navy-400">1</span>Ask a question about any module</li>
                <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-navy-100 text-[9px] font-bold text-navy-600 dark:bg-navy-500/20 dark:text-navy-400">2</span>Optionally attach a screenshot</li>
                <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-navy-100 text-[9px] font-bold text-navy-600 dark:bg-navy-500/20 dark:text-navy-400">3</span>Get step-by-step guidance instantly</li>
                <li class="flex items-start gap-2"><span class="mt-0.5 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-navy-100 text-[9px] font-bold text-navy-600 dark:bg-navy-500/20 dark:text-navy-400">4</span>Follow up with more questions</li>
            </ol>
            <p class="mt-3 text-[11px] text-ink-400 dark:text-ink-500">AI never changes your data — it only guides and recommends.</p>
        </div>
    </div>

    
    <div class="card flex min-h-0 flex-1 flex-col lg:h-[calc(100vh-16rem)]">

        
        <div class="mb-4 border-b border-ink-100 pb-4 dark:border-ink-800">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-navy-600 to-accent-600 text-white shadow-lg shadow-navy-500/25">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">VentureX AI Support Assistant</h2>
                    <p class="text-sm text-ink-400 dark:text-ink-500">Ask how to use any feature, troubleshoot errors, or get step-by-step guidance.</p>
                </div>
            </div>
        </div>

        
        <div id="support-messages" class="flex-1 space-y-4 overflow-y-auto pr-1">
            <template x-if="messages.length === 0">
                <div class="flex h-full items-center justify-center">
                    <div class="text-center max-w-md mx-auto">
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-navy-600 to-accent-600 text-white shadow-xl shadow-navy-500/25">
                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-lg font-semibold text-ink-700 dark:text-ink-200">How can I help you today?</p>
                        <p class="mt-1 text-sm text-ink-400 dark:text-ink-500">Choose a topic from the sidebar or type your question below.</p>
                        <div class="mt-6 grid grid-cols-2 gap-2 text-left">
                            <template x-for="(q, i) in quickSuggestions" :key="i">
                                <button type="button" @click="ask(q)" class="rounded-lg border border-ink-200 px-3 py-2 text-xs text-ink-600 transition hover:border-navy-400 hover:bg-navy-50 dark:border-ink-700 dark:text-ink-400 dark:hover:border-navy-500 dark:hover:bg-navy-500/10" x-text="q"></button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>

            <template x-for="(m, i) in messages" :key="i">
                <div class="flex" :class="m.role === 'user' ? 'justify-end' : 'justify-start'">
                    <div class="max-w-[85%] rounded-2xl px-4 py-3 text-sm leading-relaxed"
                        :class="m.role === 'user' ? 'rounded-br-sm bg-navy-600 text-white' : 'rounded-bl-sm bg-ink-50 text-ink-800 border border-ink-200 dark:bg-ink-800 dark:text-ink-200 dark:border-ink-700'">

                        
                        <template x-if="m.role === 'user'">
                            <div>
                                <p class="whitespace-pre-wrap" x-text="m.content"></p>
                                <template x-if="m.screenshotName">
                                    <p class="mt-2 flex items-center gap-1.5 text-xs text-navy-200">
                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span x-text="m.screenshotName"></span>
                                    </p>
                                </template>
                            </div>
                        </template>

                        
                        <template x-if="m.role === 'assistant'">
                            <div>
                                <div class="whitespace-pre-wrap prose prose-sm max-w-none prose-p:my-1 prose-headings:my-2 prose-strong:text-ink-900 dark:prose-strong:text-ink-50" x-html="formatResponse(m.content)"></div>
                                <p class="mt-3 flex items-center gap-3 text-xs text-ink-400 dark:text-ink-500 border-t border-ink-100 dark:border-ink-700 pt-2">
                                    <span class="flex items-center gap-1">
                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                        AI Powered
                                    </span>
                                    <span x-show="m.cached">Cached</span>
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <div x-show="loading" class="flex justify-start">
                <div class="max-w-[85%] rounded-2xl rounded-bl-sm bg-ink-50 px-4 py-3 text-sm text-ink-400 flex items-center gap-2.5 border border-ink-200 dark:bg-ink-800 dark:text-ink-500 dark:border-ink-700">
                    <div class="flex gap-1">
                        <span class="h-2 w-2 rounded-full bg-navy-400 animate-bounce" style="animation-delay: 0ms"></span>
                        <span class="h-2 w-2 rounded-full bg-navy-400 animate-bounce" style="animation-delay: 150ms"></span>
                        <span class="h-2 w-2 rounded-full bg-navy-400 animate-bounce" style="animation-delay: 300ms"></span>
                    </div>
                    <span>Analyzing your question</span>
                </div>
            </div>
        </div>

        
        <div class="mt-4 border-t border-ink-100 pt-4 dark:border-ink-800">

            <template x-if="screenshotPreview">
                <div class="mb-3 flex items-center gap-2 rounded-lg bg-ink-50 px-3 py-2 dark:bg-ink-800">
                    <img :src="screenshotPreview" class="h-10 w-10 rounded object-cover" alt="Screenshot preview">
                    <span class="flex-1 truncate text-xs text-ink-500" x-text="screenshotName"></span>
                    <button type="button" @click="removeScreenshot()" class="rounded p-1 text-ink-400 hover:bg-ink-200 hover:text-ink-600 dark:hover:bg-ink-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>

            <form @submit.prevent="submitQuestion()" class="flex items-end gap-2">
                <label class="shrink-0 cursor-pointer rounded-lg border border-ink-200 p-2.5 text-ink-400 transition hover:bg-ink-50 hover:text-navy-600 dark:border-ink-700 dark:text-ink-500 dark:hover:bg-ink-800 dark:hover:text-navy-400" title="Attach screenshot">
                    <input type="file" accept="image/*" class="hidden" @change="handleScreenshot($event)">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </label>
                <textarea name="question" rows="1" placeholder="How do I create a new sales order?"
                    class="input flex-1 resize-none" x-model="question"
                    @keydown.enter.prevent="if (!$event.shiftKey) submitQuestion()"
                    @input="autoResize($event.target)"></textarea>
                <button type="submit" class="btn-accent !px-4" :disabled="loading || !question.trim()">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
    function supportAssistant() {
        return {
            question: '',
            loading: false,
            messages: [],
            screenshotFile: null,
            screenshotPreview: null,
            screenshotName: '',
            quickSuggestions: [
                'How do I create a new quotation?',
                'How to add a new customer?',
                'Where can I view my invoices?',
                'How do I process a purchase order?',
                'How to check stock levels?',
                'How to set up a new user account?',
            ],
            helpTopics: [
                { label: 'Getting Started', icon: '<svg class="h-4 w-4 text-navy-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>', question: 'How do I get started with VentureX ERP & CRM? Give me a quick overview of the main modules.' },
                { label: 'CRM', icon: '<svg class="h-4 w-4 text-navy-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>', question: 'How do I manage customers, contacts, leads, and opportunities in the CRM module?' },
                { label: 'Sales', icon: '<svg class="h-4 w-4 text-navy-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6 0M9 10h6M4 5a2 2 0 012-2h12a2 2 0 012 2v16l-3-2-3 2-3-2-3 2-3-2V5z"/></svg>', question: 'Walk me through the sales workflow: quotations, sales orders, invoices, and payments.' },
                { label: 'Inventory', icon: '<svg class="h-4 w-4 text-navy-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>', question: 'How do I manage products, warehouses, and stock levels in the Inventory module?' },
                { label: 'Procurement', icon: '<svg class="h-4 w-4 text-navy-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>', question: 'How do I create purchase requisitions, send RFQs to suppliers, and create purchase orders?' },
                { label: 'Logistics', icon: '<svg class="h-4 w-4 text-navy-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13l9 5 9-5M3 13l9-5 9 5M3 13v5l9 5 9-5v-5M12 3v5m0 0l2 1.5M12 8l-2 1.5"/></svg>', question: 'How do I manage shipments, containers, and landed costs in Logistics?' },
                { label: 'Finance', icon: '<svg class="h-4 w-4 text-navy-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m-6 4h.01M12 11h.01M15 11h.01M9 15h.01M12 15h.01M15 15h.01M9 19h.01M12 19h.01M15 19h.01M4 3h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1V4a1 1 0 011-1z"/></svg>', question: 'How do I use the Finance module: chart of accounts, journal entries, receivables, and payables?' },
                { label: 'AI Features', icon: '<svg class="h-4 w-4 text-navy-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 3l.8 2.2L8 6l-2.2.8L5 9l-.8-2.2L2 6l2.2-.8L5 3zm11 0l.8 2.2L19 6l-2.2.8L16 9l-.8-2.2L13 6l2.2-.8L16 3zm-5 5l.8 2.2L14 11l-2.2.8L11 14l-.8-2.2L8 11l2.2-.8L11 8zm6 7l.8 2.2L20 18l-2.2.8L17 21l-.8-2.2L14 18l2.2-.8L17 15z"/></svg>', question: 'What AI features are available in VentureX? How do I use the AI Assistant, Copilot, and Insights?' },
                { label: 'Administration', icon: '<svg class="h-4 w-4 text-navy-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/></svg>', question: 'How do I manage users, roles, permissions, and company settings as an administrator?' },
                { label: 'API', icon: '<svg class="h-4 w-4 text-navy-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>', question: 'Does VentureX provide an API? How can I integrate with external systems?' },
            ],
            init() {
                const el = document.getElementById('support-messages');
                if (el) {
                    const observer = new MutationObserver(() => { el.scrollTop = el.scrollHeight; });
                    observer.observe(el, { childList: true, subtree: true });
                }
            },
            autoResize(el) {
                el.style.height = 'auto';
                el.style.height = Math.min(el.scrollHeight, 120) + 'px';
            },
            handleScreenshot(e) {
                const file = e.target.files[0];
                if (!file) return;
                this.screenshotFile = file;
                this.screenshotName = file.name;
                const reader = new FileReader();
                reader.onload = (ev) => { this.screenshotPreview = ev.target.result; };
                reader.readAsDataURL(file);
            },
            removeScreenshot() {
                this.screenshotFile = null;
                this.screenshotPreview = null;
                this.screenshotName = '';
            },
            askTopic(question) {
                this.ask(question);
            },
            formatResponse(content) {
                if (!content) return '';
                let html = content
                    .replace(/\[STEP\]/g, '<span class="inline-block rounded bg-navy-100 px-1.5 py-0.5 text-[11px] font-bold text-navy-700 dark:bg-navy-500/20 dark:text-navy-400">STEP</span>')
                    .replace(/\[TIP\]/g, '<span class="inline-block rounded bg-emerald-100 px-1.5 py-0.5 text-[11px] font-bold text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-400">TIP</span>')
                    .replace(/\[WARNING\]/g, '<span class="inline-block rounded bg-amber-100 px-1.5 py-0.5 text-[11px] font-bold text-amber-700 dark:bg-amber-500/20 dark:text-amber-400">WARNING</span>')
                    .replace(/\[ANSWER\]/g, '<span class="inline-block rounded bg-purple-100 px-1.5 py-0.5 text-[11px] font-bold text-purple-700 dark:bg-purple-500/20 dark:text-purple-400">ANSWER</span>')
                    .replace(/\[FACT\]/g, '<span class="inline-block rounded bg-blue-100 px-1.5 py-0.5 text-[11px] font-bold text-blue-700 dark:bg-blue-500/20 dark:text-blue-400">FACT</span>')
                    .replace(/\[RECOMMENDATION\]/g, '<span class="inline-block rounded bg-indigo-100 px-1.5 py-0.5 text-[11px] font-bold text-indigo-700 dark:bg-indigo-500/20 dark:text-indigo-400">RECOMMENDATION</span>')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/`([^`]+)`/g, '<code class="rounded bg-ink-100 px-1.5 py-0.5 text-[12px] dark:bg-ink-700">$1</code>');
                return html;
            },
            async ask(text) {
                const question = (text || this.question).trim();
                if (!question || this.loading) return;

                this.messages.push({ role: 'user', content: question, screenshotName: this.screenshotName || null });
                this.question = '';
                this.loading = true;

                const payload = new FormData();
                payload.append('question', question);
                if (this.screenshotFile) {
                    payload.append('screenshot', this.screenshotFile);
                }
                this.removeScreenshot();

                try {
                    const res = await fetch(<?php echo json_encode(route('ai.support-assistant.ask'), 15, 512) ?>, {
                        method: 'POST',
                        body: payload,
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    const data = await res.json().catch(() => ({}));
                    if (res.ok) {
                        this.messages.push({ role: 'assistant', content: data.content, cached: data.cached });
                    } else {
                        this.messages.push({ role: 'assistant', content: data.error || 'Something went wrong. Please try again.' });
                    }
                } catch (err) {
                    this.messages.push({ role: 'assistant', content: 'Could not reach the server. Please check your connection and try again.' });
                } finally {
                    this.loading = false;
                }
            },
            async submitQuestion() {
                await this.ask(this.question);
            }
        };
    }
</script>
<?php $__env->stopPush(); ?>

 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $attributes = $__attributesOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__attributesOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5863877a5171c196453bfa0bd807e410)): ?>
<?php $component = $__componentOriginal5863877a5171c196453bfa0bd807e410; ?>
<?php unset($__componentOriginal5863877a5171c196453bfa0bd807e410); ?>
<?php endif; ?>
<?php /**PATH C:\MY_ERP\resources\views/ai/support-assistant.blade.php ENDPATH**/ ?>