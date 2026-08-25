<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'AI Assistant','breadcrumbs' => [['label' => 'AI Center'], ['label' => 'Assistant']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'AI Assistant','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'AI Center'], ['label' => 'Assistant']])]); ?>

    <div class="grid gap-6 md:grid-cols-4 lg:grid-cols-4">
        <div class="lg:col-span-1 space-y-6">
            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400 dark:text-ink-500">Skills</h3>
                <div class="space-y-2" x-data="{ skill: <?php echo \Illuminate\Support\Js::from($conversation?->skill_slug)->toHtml() ?> }">
                    <template x-for="s in <?php echo \Illuminate\Support\Js::from($skills->map(fn ($skill) => ['slug' => $skill->slug, 'name' => $skill->name]))->toHtml() ?>">
                        <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-ink-200 px-3 py-2 text-sm dark:border-ink-700" :class="skill === s.slug ? 'border-navy-400 bg-navy-50 text-navy-800 dark:border-navy-500 dark:bg-navy-500/15 dark:text-navy-300' : 'text-ink-700 dark:text-ink-300'">
                            <input type="radio" name="skill" x-model="skill" :value="s.slug" class="h-4 w-4 text-accent-600 focus:ring-accent-500">
                            <span x-text="s.name"></span>
                        </label>
                    </template>
                </div>
            </div>

            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400 dark:text-ink-500">Recent Chats</h3>
                <div class="space-y-2">
                    <?php $__empty_1 = true; $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <a href="<?php echo e(route('ai.assistant', ['conversation' => $c->id])); ?>"
                           class="block truncate rounded-lg px-3 py-2 text-sm <?php echo e($conversation?->id === $c->id ? 'bg-navy-50 font-medium text-navy-700 dark:bg-navy-500/15 dark:text-navy-300' : 'text-ink-600 hover:bg-ink-50 dark:text-ink-400 dark:hover:bg-ink-800'); ?>">
                            <?php echo e($c->title); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-ink-400 dark:text-ink-500">No conversations yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="card flex min-h-0 flex-1 lg:h-[calc(100vh-16rem)] flex-col">
                <div class="mb-4 border-b border-ink-100 pb-3 dark:border-ink-800">
                    <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">VentureX ERP & CRM Assistant</h2>
                    <p class="text-sm text-ink-400 dark:text-ink-500">Responses are labelled [FACT], [CALCULATION], [ASSUMPTION] and [RECOMMENDATION]. AI analysis never approves or rejects suppliers.</p>
                </div>

                <div id="chat-messages" class="flex-1 space-y-4 overflow-y-auto pr-1">
                    <?php if($conversation): ?>
                        <?php $__currentLoopData = $conversation->messages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $message): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex <?php echo e($message->role === 'user' ? 'justify-end' : 'justify-start'); ?>">
                                <div class="max-w-[80%] rounded-2xl px-4 py-2 text-sm <?php echo e($message->role === 'user' ? 'rounded-br-sm bg-navy-600 text-white' : 'rounded-bl-sm bg-ink-100 text-ink-800 dark:bg-ink-800 dark:text-ink-200'); ?>">
                                    <p class="whitespace-pre-wrap"><?php echo e($message->content); ?></p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="flex h-full items-center justify-center">
                            <div class="text-center">
                                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-navy-600 text-white">
                                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <p class="text-lg font-semibold text-ink-700 dark:text-ink-200">How can I help you today?</p>
                                <p class="mt-1 text-sm text-ink-400 dark:text-ink-500">Ask about suppliers, scrap offers, chemistry analysis, or your procurement workflow.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <form id="chat-form" class="mt-4 flex items-end gap-2">
                    <input type="hidden" name="conversation_id" value="<?php echo e($conversation?->id); ?>" id="conversation-id">
                    <textarea name="message" id="chat-input" rows="1" placeholder="Type your question—¦" class="input flex-1 resize-none" required></textarea>
                    <button type="submit" class="btn-accent !px-4">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('chat-form');
            const input = document.getElementById('chat-input');
            const messages = document.getElementById('chat-messages');
            const conversationId = document.getElementById('conversation-id');

            function scrollBottom() {
                messages.scrollTop = messages.scrollHeight;
            }
            scrollBottom();

            form.addEventListener('submit', async function (e) {
                e.preventDefault();
                const message = input.value.trim();
                if (!message) return;

                const skill = document.querySelector('input[name="skill"]:checked');
                const payload = new FormData();
                payload.append('message', message);
                payload.append('skill_slug', skill ? skill.value : '');
                if (conversationId.value) payload.append('conversation_id', conversationId.value);

                const userBubble = document.createElement('div');
                userBubble.className = 'flex justify-end';
                userBubble.innerHTML = '<div class="max-w-[80%] rounded-2xl rounded-br-sm bg-navy-600 px-4 py-2 text-sm text-white"><p class="whitespace-pre-wrap"></p></div>';
                userBubble.querySelector('p').textContent = message;
                messages.appendChild(userBubble);
                input.value = '';
                input.style.height = 'auto';

                const pending = document.createElement('div');
                pending.className = 'flex justify-start';
                pending.innerHTML = '<div class="max-w-[80%] rounded-2xl rounded-bl-sm bg-ink-100 px-4 py-2 text-sm text-ink-400 flex items-center gap-2 dark:bg-ink-800 dark:text-ink-500"><svg class="animate-spin h-4 w-4 text-ink-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Analysing—¦</div>';
                messages.appendChild(pending);
                scrollBottom();

                const res = await fetch(<?php echo json_encode(route('ai.assistant.send'), 15, 512) ?>, { method: 'POST', body: payload, headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content } });

                pending.remove();

                const bubble = document.createElement('div');
                bubble.className = 'flex justify-start';

                if (res.ok) {
                    const data = await res.json();
                    if (data.message.conversation_id) conversationId.value = data.message.conversation_id;
                    bubble.innerHTML = '<div class="max-w-[80%] rounded-2xl rounded-bl-sm bg-ink-100 px-4 py-2 text-sm text-ink-800 dark:bg-ink-800 dark:text-ink-200"><p class="whitespace-pre-wrap"></p></div>';
                    bubble.querySelector('p').textContent = data.message.content;
                } else {
                    const data = await res.json().catch(() => ({}));
                    bubble.innerHTML = '<div class="max-w-[80%] rounded-2xl rounded-bl-sm bg-red-50 px-4 py-2 text-sm text-red-700 dark:bg-red-500/10 dark:text-red-400"><p></p></div>';
                    bubble.querySelector('p').textContent = data.error || 'Something went wrong. Please try again.';
                }
                messages.appendChild(bubble);
                scrollBottom();
            });
        });
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
<?php /**PATH C:\MY_ERP\resources\views/ai/assistant.blade.php ENDPATH**/ ?>