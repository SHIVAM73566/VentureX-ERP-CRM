<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Support Center','breadcrumbs' => [['label' => 'Administration'], ['label' => 'Support Center']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Support Center'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration'], ['label' => 'Support Center']])]); ?>

    <?php
        $icons = [
            'ticket' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'folder-open' => 'M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-1.7.9L5 19z',
            'refresh' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
            'check' => 'M5 13l4 4L19 7',
            'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
            'bug' => 'M12 12m-3 0a3 3 0 106 0 3 3 0 10-6 0M13.73 21h-3.46M20 4v2M4 4v2m13.5-4l2 2M4 6l2-2m14 14l2 2M4 18l2-2',
            'cog' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
            'filter' => 'M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z',
            'arrow-right' => 'M13 7l5 5m0 0l-5 5m5-5H6',
        ];

        $statusColor = fn ($status) => match($status) {
            'open' => 'badge-blue',
            'investigating' => 'badge-yellow',
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

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-navy-50 text-navy-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['ticket']); ?>"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900"><?php echo e($stats['total'] ?? 0); ?></p>
                        <p class="text-xs text-ink-500">Total Tickets</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-blue-50 text-blue-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['folder-open']); ?>"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900"><?php echo e($stats['open'] ?? 0); ?></p>
                        <p class="text-xs text-ink-500">Open Tickets</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['refresh']); ?>"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900"><?php echo e($stats['in_progress'] ?? 0); ?></p>
                        <p class="text-xs text-ink-500">In Progress</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-emerald-50 text-emerald-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['check']); ?>"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900"><?php echo e($stats['resolved_today'] ?? 0); ?></p>
                        <p class="text-xs text-ink-500">Resolved Today</p>
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3">
                    <span class="stat-icon bg-violet-50 text-violet-600">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['clock']); ?>"/></svg>
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-ink-900"><?php echo e($stats['avg_response_time'] ?? '—'); ?></p>
                        <p class="text-xs text-ink-500">Avg Response Time</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="<?php echo e(route('admin.errors.index')); ?>" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium bg-red-50 text-red-700 hover:bg-red-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['bug']); ?>"/></svg>
                Error Center
            </a>
            <a href="<?php echo e(route('admin.control-center.index')); ?>" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium bg-navy-50 text-navy-700 hover:bg-navy-100">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['cog']); ?>"/></svg>
                Control Center
            </a>
        </div>

        <div class="card" x-data="{ filter: '<?php echo e(request('priority', '')); ?>' }">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800">Recent Tickets</h2>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-ink-400">Filter:</span>
                    <div class="flex flex-wrap gap-1">
                        <a href="<?php echo e(route('admin.support.index')); ?>" class="rounded-full px-2.5 py-1 text-xs font-medium <?php echo e(request('priority', '') === '' ? 'bg-navy-100 text-navy-700' : 'bg-ink-100 text-ink-500 hover:bg-ink-200'); ?>">All</a>
                        <?php $__currentLoopData = ['urgent', 'high', 'medium', 'low']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(route('admin.support.index', ['priority' => $p])); ?>" class="rounded-full px-2.5 py-1 text-xs font-medium <?php echo e(request('priority') === $p ? match($p) { 'urgent' => 'bg-red-100 text-red-700', 'high' => 'bg-amber-100 text-amber-700', 'medium' => 'bg-yellow-100 text-yellow-700', 'low' => 'bg-emerald-100 text-emerald-700', default => 'bg-navy-100 text-navy-700' } : 'bg-ink-100 text-ink-500 hover:bg-ink-200'); ?>"><?php echo e(ucfirst($p)); ?></a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Subject</th>
                            <th>Customer</th>
                            <th>Module</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="font-mono text-xs text-ink-500"><?php echo e($ticket->ticket_number); ?></td>
                                <td class="font-medium text-ink-800"><?php echo e(Str::limit($ticket->subject, 40)); ?></td>
                                <td class="text-ink-600"><?php echo e($ticket->customer?->name ?? '—'); ?></td>
                                <td><span class="badge badge-gray"><?php echo e($ticket->module ?? 'General'); ?></span></td>
                                <td><span class="<?php echo e($priorityColor($ticket->priority)); ?>"><?php echo e(ucfirst($ticket->priority)); ?></span></td>
                                <td><span class="<?php echo e($statusColor($ticket->status)); ?>"><?php echo e(str_replace('_', ' ', ucfirst($ticket->status))); ?></span></td>
                                <td class="text-xs text-ink-400"><?php echo e($ticket->created_at->diffForHumans()); ?></td>
                                <td class="text-right">
                                    <a href="<?php echo e(route('admin.support.tickets.show', $ticket)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="py-8 text-center text-sm text-ink-400">No tickets found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4"><?php echo e($tickets->withQueryString()->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\support\index.blade.php ENDPATH**/ ?>