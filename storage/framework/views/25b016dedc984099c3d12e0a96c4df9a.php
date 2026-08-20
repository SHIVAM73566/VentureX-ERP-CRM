<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Ticket #' . ($ticket->ticket_number ?? $ticket->id),'breadcrumbs' => [
        ['label' => 'Support Center', 'url' => route('support.index')],
        ['label' => 'My Tickets', 'url' => route('support.tickets.index')],
        ['label' => 'Ticket #' . ($ticket->ticket_number ?? $ticket->id)],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Ticket #' . ($ticket->ticket_number ?? $ticket->id)),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Support Center', 'url' => route('support.index')],
        ['label' => 'My Tickets', 'url' => route('support.tickets.index')],
        ['label' => 'Ticket #' . ($ticket->ticket_number ?? $ticket->id)],
    ])]); ?>

    <div class="mx-auto max-w-4xl space-y-6">

        
        <div class="card">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-mono text-xs font-semibold text-navy-600">#<?php echo e($ticket->ticket_number ?? $ticket->id); ?></span>
                        <?php
                            $statusStyles = match($ticket->status) {
                                'open' => 'bg-blue-100 text-blue-700',
                                'investigating' => 'bg-amber-100 text-amber-700',
                                'in_progress' => 'bg-indigo-100 text-indigo-700',
                                'resolved' => 'bg-green-100 text-green-700',
                                'closed' => 'bg-ink-200 text-ink-500',
                                default => 'bg-ink-100 text-ink-600',
                            };
                            $priorityStyles = match($ticket->priority) {
                                'urgent' => 'bg-red-100 text-red-700',
                                'high' => 'bg-orange-100 text-orange-700',
                                'medium' => 'bg-yellow-100 text-yellow-700',
                                'low' => 'bg-green-100 text-green-700',
                                default => 'bg-ink-100 text-ink-600',
                            };
                        ?>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold <?php echo e($statusStyles); ?>"><?php echo e(str_replace('_', ' ', ucfirst($ticket->status))); ?></span>
                        <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold <?php echo e($priorityStyles); ?>"><?php echo e(ucfirst($ticket->priority)); ?></span>
                    </div>
                    <h2 class="mt-2 text-xl font-bold text-ink-900"><?php echo e($ticket->subject); ?></h2>
                </div>

                <?php if($ticket->status === 'resolved'): ?>
                    <form action="<?php echo e(route('support.tickets.update', $ticket)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <input type="hidden" name="status" value="closed">
                        <button type="submit" onclick="return confirm('Are you sure you want to close this ticket?')" class="inline-flex items-center gap-2 rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Close Ticket
                        </button>
                    </form>
                <?php endif; ?>
            </div>

            <div class="mt-4 flex flex-wrap gap-x-6 gap-y-2 border-t border-ink-100 pt-4 text-xs text-ink-500">
                <span><strong class="text-ink-600">Category:</strong> <?php echo e(ucfirst(str_replace('_', ' ', $ticket->category ?? 'General'))); ?></span>
                <span><strong class="text-ink-600">Module:</strong> <?php echo e(ucfirst($ticket->module ?? 'General')); ?></span>
                <span><strong class="text-ink-600">Created:</strong> <?php echo e($ticket->created_at?->format('M d, Y \a\t g:i A') ?? ''); ?></span>
                <span><strong class="text-ink-600">Updated:</strong> <?php echo e($ticket->updated_at?->diffForHumans() ?? ''); ?></span>
            </div>
        </div>

        
        <div class="space-y-4">
            <h3 class="text-sm font-bold text-ink-700">Conversation</h3>

            <?php $__empty_1 = true; $__currentLoopData = $ticket->replies->filter(fn ($r) => !$r->is_internal); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php $isAdmin = $reply->user->hasRole('super_admin') || $reply->user->hasRole('admin'); ?>
                <div class="flex <?php echo e($isAdmin ? 'justify-start' : 'justify-end'); ?>">
                    <div class="max-w-[80%] <?php echo e($isAdmin ? 'bg-ink-100' : 'bg-navy-600 text-white'); ?> rounded-2xl px-5 py-3.5">
                        <div class="flex items-center gap-2">
                            <?php if($isAdmin): ?>
                                <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-navy-600 text-[10px] font-bold text-white">S</div>
                            <?php endif; ?>
                            <span class="text-xs font-semibold <?php echo e($isAdmin ? 'text-ink-600' : 'text-navy-200'); ?>"><?php echo e($reply->user->displayName() ?? 'Support'); ?></span>
                            <span class="text-xs <?php echo e($isAdmin ? 'text-ink-400' : 'text-navy-300'); ?>"><?php echo e($reply->created_at?->diffForHumans() ?? ''); ?></span>
                        </div>
                        <p class="mt-2 text-sm <?php echo e($isAdmin ? 'text-ink-700' : 'text-white'); ?>"><?php echo nl2br(e($reply->message)); ?></p>
                        <?php if($reply->attachments && count($reply->attachments) > 0): ?>
                            <?php $__currentLoopData = $reply->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $attachment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e(asset('storage/' . $attachment)); ?>" target="_blank" class="mt-2 inline-flex items-center gap-1 text-xs <?php echo e($isAdmin ? 'text-navy-600' : 'text-navy-200'); ?> hover:underline">
                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                                    View attachment
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="card py-8 text-center text-sm text-ink-400">
                    No replies yet. Submit a message below to start the conversation.
                </div>
            <?php endif; ?>
        </div>

        
        <?php if($ticket->description): ?>
            <div class="card border-l-4 border-navy-400">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-400">Your original message</p>
                <p class="mt-2 text-sm text-ink-700"><?php echo nl2br(e($ticket->description)); ?></p>
            </div>
        <?php endif; ?>

        
        <?php if(!in_array($ticket->status, ['closed'])): ?>
            <form action="<?php echo e(route('support.tickets.reply', $ticket)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold text-ink-700">Reply</h3>
                    <textarea name="message" rows="3" required
                        placeholder="Type your reply here..."
                        class="w-full rounded-lg border border-ink-200 bg-white px-3 py-2.5 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:outline-none focus:ring-2 focus:ring-navy-500/20"></textarea>
                    <div class="mt-3 flex items-center justify-end">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-navy-600 px-5 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-navy-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send Reply
                        </button>
                    </div>
                </div>
            </form>
        <?php else: ?>
            <div class="card py-8 text-center text-sm text-ink-400">
                This ticket is closed. If you need further assistance, please <a href="<?php echo e(route('support.tickets.create')); ?>" class="font-semibold text-navy-600 hover:underline">create a new ticket</a>.
            </div>
        <?php endif; ?>
    </div>
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
<?php /**PATH C:\MY_ERP\resources\views\support\tickets\show.blade.php ENDPATH**/ ?>