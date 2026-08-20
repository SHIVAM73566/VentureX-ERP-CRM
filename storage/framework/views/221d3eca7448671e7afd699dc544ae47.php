<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Export Center','breadcrumbs' => [['label' => 'Administration'], ['label' => 'Export Center']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Export Center'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration'], ['label' => 'Export Center']])]); ?>

    <div class="mx-auto max-w-5xl space-y-6">
        <form method="POST" action="<?php echo e(route('admin.exports.store')); ?>" class="card">
            <?php echo csrf_field(); ?>
            <h2 class="mb-1 text-lg font-bold text-ink-900">Request a data export</h2>
            <p class="mb-4 text-sm text-ink-500">Sensitive datasets require super-admin approval before the export is generated. Exports are delivered as expiring, download-limited signed links.</p>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label for="data_type" class="label">Data type</label>
                    <select id="data_type" name="data_type" class="input" required>
                        <?php $__currentLoopData = $sources; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $source): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($source); ?>"><?php echo e(ucfirst($source)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label for="format" class="label">Format</label>
                    <select id="format" name="format" class="input" required>
                        <option value="csv">CSV</option>
                        <option value="xlsx">Excel (XLSX)</option>
                    </select>
                </div>
                <div>
                    <label for="reason" class="label">Reason (required)</label>
                    <input id="reason" name="reason" class="input" placeholder="e.g. Q3 reconciliation" required maxlength="500">
                </div>
            </div>

            <?php $__errorArgs = ['data_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <?php $__errorArgs = ['reason'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-2 text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <div class="mt-4 flex justify-end">
                <button type="submit" class="btn-accent">Request export</button>
            </div>
        </form>

        <div class="card">
            <h2 class="mb-4 text-lg font-bold text-ink-900">Export requests</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-ink-200 text-xs uppercase tracking-wide text-ink-400">
                            <th class="py-2 pr-4">Ref</th>
                            <th class="py-2 pr-4">Data</th>
                            <th class="py-2 pr-4">Status</th>
                            <th class="py-2 pr-4">Requested by</th>
                            <th class="py-2 pr-4">Records</th>
                            <th class="py-2 pr-4">Expires</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $export): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="border-b border-ink-100">
                                <td class="py-2.5 pr-4 font-mono text-xs text-ink-600"><?php echo e($export->export_id); ?></td>
                                <td class="py-2.5 pr-4">
                                    <?php echo e(ucfirst($export->data_type)); ?>

                                    <span class="ml-1 badge <?php echo e($export->sensitivity === 'restricted' ? 'badge-amber' : 'badge-gray'); ?>"><?php echo e($export->sensitivity); ?></span>
                                </td>
                                <td class="py-2.5 pr-4">
                                    <span class="badge <?php echo e($export->status === 'ready' ? 'badge-green' : ($export->status === 'pending' ? 'badge-amber' : ($export->status === 'rejected' ? 'badge-red' : 'badge-gray'))); ?>"><?php echo e($export->status); ?></span>
                                </td>
                                <td class="py-2.5 pr-4 text-xs text-ink-600"><?php echo e($export->user?->email ?? '—'); ?></td>
                                <td class="py-2.5 pr-4"><?php echo e($export->record_count ?: '—'); ?></td>
                                <td class="py-2.5 pr-4 text-xs text-ink-500"><?php echo e($export->expires_at?->diffForHumans() ?? '—'); ?></td>
                                <td class="py-2.5">
                                    <div class="flex items-center justify-end gap-2">
                                        <?php if($export->status === 'pending' && $canApprove): ?>
                                            <form method="POST" action="<?php echo e(route('admin.exports.approve', $export)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn-secondary px-3 py-1.5 text-xs">Approve</button>
                                            </form>
                                            <form method="POST" action="<?php echo e(route('admin.exports.reject', $export)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="btn-danger px-3 py-1.5 text-xs">Reject</button>
                                            </form>
                                        <?php elseif($export->status === 'ready'): ?>
                                            <a href="<?php echo e(route('admin.exports.download', $export)); ?>" class="btn-secondary px-3 py-1.5 text-xs">Download (<?php echo e($export->download_count); ?>/<?php echo e($export->max_downloads); ?>)</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-sm text-ink-400">No export requests yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4"><?php echo e($requests->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\exports\index.blade.php ENDPATH**/ ?>