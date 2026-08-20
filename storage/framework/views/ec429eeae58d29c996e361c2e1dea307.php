<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Ticket ' . $ticket->ticket_number,'breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.support.index')], ['label' => 'Support', 'url' => route('admin.support.index')], ['label' => $ticket->ticket_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Ticket ' . $ticket->ticket_number),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.support.index')], ['label' => 'Support', 'url' => route('admin.support.index')], ['label' => $ticket->ticket_number]])]); ?>

    <?php
        $statusColor = fn ($status) => match($status) {
            'open' => 'badge-blue',
            'in_progress' => 'badge-amber',
            'resolved' => 'badge-green',
            'closed' => 'badge-gray',
            default => 'badge-gray',
        };

        $priorityColor = fn ($priority) => match($priority) {
            'urgent' => 'badge-red',
            'high' => 'badge-amber',
            'medium' => 'badge-yellow',
            'low' => 'badge-green',
            default => 'badge-gray',
        };
    ?>

    <div class="mx-auto max-w-6xl space-y-6" x-data="{ replyForm: false, noteForm: false }">

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">

                <div class="card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-bold text-ink-900"><?php echo e($ticket->subject); ?></h2>
                            <p class="mt-1 text-sm text-ink-500">Created <?php echo e($ticket->created_at->format('d M Y, H:i')); ?> by <?php echo e($ticket->user?->name ?? 'System'); ?></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="<?php echo e($priorityColor($ticket->priority)); ?>"><?php echo e(ucfirst($ticket->priority)); ?></span>
                            <span class="<?php echo e($statusColor($ticket->status)); ?>"><?php echo e(str_replace('_', ' ', ucfirst($ticket->status))); ?></span>
                        </div>
                    </div>
                    <div class="mt-4 rounded-lg border border-ink-100 bg-ink-50/50 p-4">
                        <p class="whitespace-pre-wrap text-sm text-ink-700"><?php echo e($ticket->description); ?></p>
                    </div>
                </div>

                <?php if($ticket->errorReport): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3 class="text-sm font-bold text-ink-800">Attached Error Report</h3>
                            <a href="<?php echo e(route('admin.errors.show', $ticket->errorReport)); ?>" class="text-xs font-medium text-navy-600 hover:text-navy-500">View in Error Center</a>
                        </div>
                        <div class="p-5 space-y-2">
                            <div class="flex items-center gap-3 text-sm">
                                <span class="text-ink-400">Error:</span>
                                <span class="badge badge-red"><?php echo e($ticket->errorReport->error_type); ?></span>
                                <span class="text-ink-600"><?php echo e(Str::limit($ticket->errorReport->error_message, 60)); ?></span>
                            </div>
                            <div class="flex items-center gap-3 text-sm">
                                <span class="text-ink-400">Occurrences:</span>
                                <span class="text-ink-800 font-medium"><?php echo e($ticket->errorReport->occurrence_count); ?></span>
                                <span class="text-ink-400 ml-4">Last seen:</span>
                                <span class="text-ink-600"><?php echo e($ticket->errorReport->last_seen_at?->diffForHumans() ?? '--'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-bold text-ink-800">Conversation</h3>
                    </div>
                    <div class="divide-y divide-ink-100">
                        <?php $__empty_1 = true; $__currentLoopData = $ticket->replies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="p-5 <?php echo e($reply->is_internal ? 'bg-amber-50/40' : ''); ?>">
                                <div class="flex items-center justify-between gap-3">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold <?php echo e($reply->is_admin ? 'bg-navy-100 text-navy-700' : 'bg-ink-100 text-ink-600'); ?>">
                                            <?php echo e(substr($reply->user?->name ?? 'S', 0, 1)); ?>

                                        </span>
                                        <span class="text-sm font-semibold text-ink-800"><?php echo e($reply->user?->name ?? 'System'); ?></span>
                                        <?php if($reply->is_admin): ?>
                                            <span class="badge badge-blue text-[10px]">Admin</span>
                                        <?php endif; ?>
                                        <?php if($reply->is_internal): ?>
                                            <span class="badge badge-yellow text-[10px]">Internal Note</span>
                                        <?php endif; ?>
                                    </div>
                                    <span class="text-xs text-ink-400"><?php echo e($reply->created_at->diffForHumans()); ?></span>
                                </div>
                                <div class="mt-2 whitespace-pre-wrap pl-9 text-sm text-ink-700"><?php echo e($reply->body); ?></div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="p-6 text-center text-sm text-ink-400">No replies yet.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="p-5">
                        <div x-show="!replyForm" x-cloak>
                            <button @click="replyForm = true; noteForm = false" class="btn-primary">Add Reply</button>
                            <button @click="noteForm = true; replyForm = false" class="btn-secondary ml-2">Add Internal Note</button>
                        </div>

                        <div x-show="replyForm" x-cloak>
                            <h4 class="mb-3 text-sm font-bold text-ink-800">Add Reply</h4>
                            <form method="POST" action="<?php echo e(route('admin.support.replies.store', $ticket)); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="is_internal" value="0">
                                <textarea name="body" class="input" rows="4" placeholder="Type your reply..." required></textarea>
                                <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="mt-3 flex items-center gap-2">
                                    <button type="submit" class="btn-primary">Send Reply</button>
                                    <button type="button" @click="replyForm = false" class="btn-secondary">Cancel</button>
                                </div>
                            </form>
                        </div>

                        <div x-show="noteForm" x-cloak>
                            <h4 class="mb-3 text-sm font-bold text-ink-800">Add Internal Note</h4>
                            <form method="POST" action="<?php echo e(route('admin.support.replies.store', $ticket)); ?>">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="is_internal" value="1">
                                <textarea name="body" class="input border-amber-300 focus:border-amber-500 focus:ring-amber-500/20" rows="4" placeholder="Internal note (only visible to admins)..." required></textarea>
                                <?php $__errorArgs = ['body'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                                <div class="mt-3 flex items-center gap-2">
                                    <button type="submit" class="btn-accent">Save Note</button>
                                    <button type="button" @click="noteForm = false" class="btn-secondary">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Ticket Details</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd class="font-mono text-ink-800"><?php echo e($ticket->ticket_number); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="<?php echo e($statusColor($ticket->status)); ?>"><?php echo e(str_replace('_', ' ', ucfirst($ticket->status))); ?></span></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Priority</dt><dd><span class="<?php echo e($priorityColor($ticket->priority)); ?>"><?php echo e(ucfirst($ticket->priority)); ?></span></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Category</dt><dd class="text-ink-700"><?php echo e($ticket->category ?? '--'); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Module</dt><dd><span class="badge badge-gray"><?php echo e($ticket->module ?? 'General'); ?></span></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Created by</dt><dd class="text-ink-700"><?php echo e($ticket->creator?->name ?? '--'); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Assigned to</dt><dd class="text-ink-700"><?php echo e($ticket->assignee?->name ?? 'Unassigned'); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Replies</dt><dd class="text-ink-700"><?php echo e($ticket->replies_count ?? $ticket->replies->count()); ?></dd></div>
                    </dl>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Update Status</h3>
                    <form method="POST" action="<?php echo e(route('admin.support.update', $ticket)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="space-y-3">
                            <div>
                                <label for="status" class="label">Status</label>
                                <select id="status" name="status" class="input">
                                    <?php $__currentLoopData = ['open', 'in_progress', 'resolved', 'closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($status); ?>" <?php if($ticket->status === $status): echo 'selected'; endif; ?>><?php echo e(str_replace('_', ' ', ucfirst($status))); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div>
                                <label for="assignee_id" class="label">Assign to</label>
                                <select id="assignee_id" name="assignee_id" class="input">
                                    <option value="">Unassigned</option>
                                    <?php $__currentLoopData = $admins; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admin): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($admin->id); ?>" <?php if($ticket->assignee_id === $admin->id): echo 'selected'; endif; ?>><?php echo e($admin->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <button type="submit" class="btn-primary w-full">Update Ticket</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\support\show.blade.php ENDPATH**/ ?>