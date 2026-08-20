<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Error Detail','breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.errors.index')], ['label' => 'Error Center', 'url' => route('admin.errors.index')], ['label' => $error->error_type]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Error Detail'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.errors.index')], ['label' => 'Error Center', 'url' => route('admin.errors.index')], ['label' => $error->error_type]])]); ?>

    <?php
        $errorStatusColor = fn ($status) => match($status) {
            'new' => 'badge-red',
            'investigating' => 'badge-yellow',
            'fixed' => 'badge-green',
            'ignored' => 'badge-gray',
            default => 'badge-gray',
        };
    ?>

    <div class="mx-auto max-w-6xl space-y-6">

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">

                <div class="card">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="badge badge-red"><?php echo e($error->error_type); ?></span>
                                <span class="<?php echo e($errorStatusColor($error->status)); ?>"><?php echo e(ucfirst($error->status)); ?></span>
                            </div>
                            <p class="mt-2 text-sm text-ink-500"><?php echo e($error->error_message); ?></p>
                        </div>
                        <span class="text-xs text-ink-400">First seen <?php echo e($error->created_at->diffForHumans()); ?></span>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2 text-sm">
                        <div class="flex justify-between"><span class="text-ink-400">Module</span><span class="badge badge-gray"><?php echo e($error->module ?? '--'); ?></span></div>
                        <div class="flex justify-between"><span class="text-ink-400">Controller</span><span class="font-mono text-xs text-ink-600"><?php echo e($error->controller ?? '--'); ?></span></div>
                        <div class="flex justify-between"><span class="text-ink-400">Action</span><span class="font-mono text-xs text-ink-600"><?php echo e($error->action ?? '--'); ?></span></div>
                        <div class="flex justify-between"><span class="text-ink-400">Route</span><span class="font-mono text-xs text-ink-600"><?php echo e($error->route ?? '--'); ?></span></div>
                        <div class="flex justify-between"><span class="text-ink-400">HTTP Method</span><span class="font-mono text-xs text-ink-600"><?php echo e($error->http_method ?? '--'); ?></span></div>
                        <div class="flex justify-between"><span class="text-ink-400">HTTP Status</span><span class="font-mono text-xs text-ink-600"><?php echo e($error->http_status ?? '--'); ?></span></div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Stack Trace</h3>
                    <div class="overflow-x-auto rounded-lg bg-ink-950 p-4">
                        <pre class="font-mono text-xs text-emerald-400 whitespace-pre-wrap break-words"><?php echo e($error->stack_trace ?? 'No stack trace available.'); ?></pre>
                    </div>
                </div>

                <?php if($error->request_data): ?>
                    <div class="card">
                        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Request Data (Sanitized)</h3>
                        <div class="overflow-x-auto rounded-lg bg-ink-50 p-4">
                            <pre class="font-mono text-xs text-ink-700 whitespace-pre-wrap break-words"><?php echo e(is_array($error->request_data) ? json_encode($error->request_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $error->request_data); ?></pre>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-bold text-ink-800">Diagnosis</h3>
                    </div>
                    <div class="p-5">
                        <form method="POST" action="<?php echo e(route('admin.errors.update', $error)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div>
                                <label for="diagnosis" class="label">Diagnosis Notes</label>
                                <textarea id="diagnosis" name="diagnosis" class="input" rows="4" placeholder="Describe the root cause, investigation notes, or any findings..."><?php echo e($error->diagnosis); ?></textarea>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <button type="submit" class="btn-primary">Save Diagnosis</button>
                                <button type="submit" name="action" value="investigating" class="btn-secondary">Mark Investigating</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-bold text-ink-800">Resolution</h3>
                    </div>
                    <div class="p-5">
                        <form method="POST" action="<?php echo e(route('admin.errors.update', $error)); ?>">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('PUT'); ?>
                            <div>
                                <label for="resolution" class="label">Fix Description</label>
                                <textarea id="resolution" name="resolution" class="input" rows="4" placeholder="Describe the fix applied..."><?php echo e($error->resolution); ?></textarea>
                            </div>
                            <div class="mt-3 flex items-center gap-2">
                                <button type="submit" class="btn-accent">Save & Mark Fixed</button>
                                <button type="submit" name="action" value="ignored" class="btn-secondary">Mark Ignored</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Error Info</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-400">Type</dt><dd><span class="badge badge-red"><?php echo e($error->error_type); ?></span></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="<?php echo e($errorStatusColor($error->status)); ?>"><?php echo e(ucfirst($error->status)); ?></span></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Occurrences</dt><dd class="font-medium text-ink-800"><?php echo e($error->occurrence_count); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">First Seen</dt><dd class="text-ink-600"><?php echo e($error->created_at->format('d M Y H:i')); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Last Seen</dt><dd class="text-ink-600"><?php echo e($error->last_seen_at?->format('d M Y H:i') ?? '--'); ?></dd></div>
                    </dl>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Server Info</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-400">App Version</dt><dd class="font-mono text-xs text-ink-600"><?php echo e($error->app_version ?? '--'); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">PHP Version</dt><dd class="font-mono text-xs text-ink-600"><?php echo e($error->php_version ?? '--'); ?></dd></div>
                        <div class="flex justify-between"><dt class="text-ink-400">Laravel Version</dt><dd class="font-mono text-xs text-ink-600"><?php echo e($error->laravel_version ?? '--'); ?></dd></div>
                    </dl>
                </div>

                <?php if($error->ticket): ?>
                    <div class="card">
                        <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Related Ticket</h3>
                        <a href="<?php echo e(route('admin.support.tickets.show', $error->ticket)); ?>" class="flex items-center gap-3 rounded-lg border border-ink-100 p-3 hover:bg-ink-50">
                            <span class="badge badge-blue"><?php echo e($error->ticket->ticket_number); ?></span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-ink-800"><?php echo e($error->ticket->subject); ?></p>
                                <p class="text-xs text-ink-400"><?php echo e(ucfirst($error->ticket->status)); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\errors\show.blade.php ENDPATH**/ ?>