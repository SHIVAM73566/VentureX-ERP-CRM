<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Error Center','breadcrumbs' => [['label' => 'Administration'], ['label' => 'Error Center']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Error Center'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration'], ['label' => 'Error Center']])]); ?>

    <?php
        $icons = [
            'bug' => 'M12 12m-3 0a3 3 0 106 0 3 3 0 10-6 0M13.73 21h-3.46M20 4v2M4 4v2m13.5-4l2 2M4 6l2-2m14 14l2 2M4 18l2-2',
            'alert' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z',
            'check-circle' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'magnify' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        ];

        $errorStatusColor = fn ($status) => match($status) {
            'new' => 'badge-red',
            'investigating' => 'badge-yellow',
            'fixed' => 'badge-green',
            'ignored' => 'badge-gray',
            default => 'badge-gray',
        };
    ?>

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-red-50 text-red-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['bug']); ?>"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900"><?php echo e($stats['new'] ?? 0); ?></p>
                        <p class="text-xs text-ink-500">New Errors</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['alert']); ?>"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900"><?php echo e($stats['investigating'] ?? 0); ?></p>
                        <p class="text-xs text-ink-500">Investigating</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['check-circle']); ?>"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900"><?php echo e($stats['fixed'] ?? 0); ?></p>
                        <p class="text-xs text-ink-500">Fixed</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-navy-50 text-navy-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900"><?php echo e($stats['total_occurrences'] ?? 0); ?></p>
                        <p class="text-xs text-ink-500">Total Occurrences</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800">Errors</h2>
                <form method="GET" action="<?php echo e(route('admin.errors.index')); ?>" class="flex flex-wrap items-center gap-2">
                    <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search errors..." class="input max-w-xs">
                    <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        <?php $__currentLoopData = ['new', 'investigating', 'fixed', 'ignored']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s); ?>" <?php if(request('status') === $s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="module" class="input max-w-[10rem]" onchange="this.form.submit()">
                        <option value="">All modules</option>
                        <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($module); ?>" <?php if(request('module') === $module): echo 'selected'; endif; ?>><?php echo e($module); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="submit" class="btn-secondary">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Error Type</th>
                            <th>Message</th>
                            <th>Module</th>
                            <th>Occurrences</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $errors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <span class="badge badge-red"><?php echo e($error->error_type); ?></span>
                                </td>
                                <td class="max-w-xs truncate font-medium text-ink-800"><?php echo e($error->error_message); ?></td>
                                <td><span class="badge badge-gray"><?php echo e($error->module ?? '--'); ?></span></td>
                                <td class="font-medium text-ink-800"><?php echo e($error->occurrence_count); ?></td>
                                <td><span class="<?php echo e($errorStatusColor($error->status)); ?>"><?php echo e(ucfirst($error->status)); ?></span></td>
                                <td class="text-xs text-ink-400"><?php echo e($error->last_seen_at?->diffForHumans() ?? '--'); ?></td>
                                <td class="text-right">
                                    <a href="<?php echo e(route('admin.errors.show', $error)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="py-8 text-center text-sm text-ink-400">No errors found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4"><?php echo e($errors->withQueryString()->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\errors\index.blade.php ENDPATH**/ ?>