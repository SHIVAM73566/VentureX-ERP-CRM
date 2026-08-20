<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Audit Logs','breadcrumbs' => [['label' => 'Admin'], ['label' => 'Audit Logs']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Audit Logs','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Admin'], ['label' => 'Audit Logs']])]); ?>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="<?php echo e(route('admin.audit-logs.index')); ?>" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search module, event or user..." class="input max-w-xs" />
            <select name="module" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All modules</option>
                <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($module); ?>" <?php if(request('module') === $module): echo 'selected'; endif; ?>><?php echo e($module); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="event" class="input max-w-[8rem]" onchange="this.form.submit()">
                <option value="">All events</option>
                <?php $__currentLoopData = $events; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($event); ?>" <?php if(request('event') === $event): echo 'selected'; endif; ?>><?php echo e($event); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="user_id" class="input max-w-[12rem]" onchange="this.form.submit()">
                <option value="">All users</option>
                <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($user->id); ?>" <?php if(request('user_id') == $user->id): echo 'selected'; endif; ?>><?php echo e($user->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <input type="date" name="date" value="<?php echo e(request('date')); ?>" class="input max-w-[10rem]" onchange="this.form.submit()" />
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Event</th><th>Module</th><th>Record</th><th>User</th><th>IP</th><th>When</th><th></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><span class="badge-<?php echo e($log->event === 'create' ? 'green' : ($log->event === 'update' ? 'blue' : ($log->event === 'delete' ? 'red' : 'amber'))); ?>"><?php echo e($log->event); ?></span></td>
                            <td class="font-medium text-ink-800"><?php echo e($log->module); ?></td>
                            <td><span class="text-xs"><?php echo e($log->record_type); ?><span class="text-ink-400">#<?php echo e($log->record_id ?? '—'); ?></span></span></td>
                            <td><?php echo e($log->user?->name ?? 'System'); ?></td>
                            <td class="font-mono text-xs text-ink-400"><?php echo e($log->ip ?? '—'); ?></td>
                            <td><?php echo e($log->created_at?->format('d M Y H:i')); ?></td>
                            <td class="text-right"><a href="<?php echo e(route('admin.audit-logs.show', $log)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No audit logs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4"><?php echo e($logs->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\audit-logs\index.blade.php ENDPATH**/ ?>