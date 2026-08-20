<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => $customer->name,'breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Customers', 'url' => route('admin.control-center.customers')], ['label' => $customer->name]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($customer->name),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Customers', 'url' => route('admin.control-center.customers')], ['label' => $customer->name]])]); ?>

    <?php
        $licenseTierColor = fn ($tier) => match($tier) {
            'enterprise' => 'badge-violet',
            'professional' => 'badge-blue',
            'starter' => 'badge-green',
            default => 'badge-gray',
        };

        $licenseStatusColor = fn ($status) => match($status) {
            'active' => 'badge-green',
            'trial' => 'badge-blue',
            'expired' => 'badge-red',
            'suspended' => 'badge-gray',
            default => 'badge-gray',
        };

        $statusColor = fn ($status) => match($status) {
            'open' => 'badge-blue',
            'in_progress' => 'badge-amber',
            'resolved' => 'badge-green',
            'closed' => 'badge-gray',
            default => 'badge-gray',
        };

        $installStatusColor = fn ($status) => match($status) {
            'active' => 'badge-green',
            'inactive' => 'badge-gray',
            'expired' => 'badge-red',
            default => 'badge-gray',
        };
    ?>

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="space-y-6">
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Company Info</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-ink-400">Name</dt><dd class="font-medium text-ink-800"><?php echo e($customer->name); ?></dd></div>
                        <?php if($customer->email): ?><div class="flex justify-between"><dt class="text-ink-400">Email</dt><dd class="text-ink-600"><?php echo e($customer->email); ?></dd></div><?php endif; ?>
                        <?php if($customer->phone): ?><div class="flex justify-between"><dt class="text-ink-400">Phone</dt><dd class="text-ink-600"><?php echo e($customer->phone); ?></dd></div><?php endif; ?>
                        <?php if($customer->website): ?><div class="flex justify-between"><dt class="text-ink-400">Website</dt><dd class="text-ink-600"><?php echo e($customer->website); ?></dd></div><?php endif; ?>
                        <?php if($customer->country): ?><div class="flex justify-between"><dt class="text-ink-400">Country</dt><dd class="text-ink-600"><?php echo e($customer->country->name); ?></dd></div><?php endif; ?>
                        <div class="flex justify-between"><dt class="text-ink-400">Last Active</dt><dd class="text-ink-600"><?php echo e($customer->last_active_at?->diffForHumans() ?? '--'); ?></dd></div>
                    </dl>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">License Details</h3>
                    <?php if($customer->license): ?>
                        <dl class="space-y-2 text-sm">
                            <div class="flex justify-between"><dt class="text-ink-400">Key</dt><dd class="font-mono text-xs text-ink-600"><?php echo e(Str::limit($customer->license->key, 20)); ?></dd></div>
                            <div class="flex justify-between"><dt class="text-ink-400">Tier</dt><dd><span class="<?php echo e($licenseTierColor($customer->license->tier)); ?>"><?php echo e(ucfirst($customer->license->tier)); ?></span></dd></div>
                            <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="<?php echo e($licenseStatusColor($customer->license->status)); ?>"><?php echo e(ucfirst($customer->license->status)); ?></span></dd></div>
                            <div class="flex justify-between"><dt class="text-ink-400">Expires</dt><dd class="text-ink-600"><?php echo e($customer->license->expires_at?->format('d M Y') ?? 'Never'); ?></dd></div>
                        </dl>
                    <?php else: ?>
                        <p class="text-sm text-ink-400">No license found.</p>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Error Summary</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3 text-center">
                            <p class="text-xl font-bold text-red-600"><?php echo e($errorCounts['new'] ?? 0); ?></p>
                            <p class="text-xs font-semibold text-red-700">New</p>
                        </div>
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-center">
                            <p class="text-xl font-bold text-amber-600"><?php echo e($errorCounts['investigating'] ?? 0); ?></p>
                            <p class="text-xs font-semibold text-amber-700">Investigating</p>
                        </div>
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3 text-center">
                            <p class="text-xl font-bold text-emerald-600"><?php echo e($errorCounts['fixed'] ?? 0); ?></p>
                            <p class="text-xs font-semibold text-emerald-700">Fixed</p>
                        </div>
                        <div class="rounded-lg border border-ink-200 bg-ink-50 p-3 text-center">
                            <p class="text-xl font-bold text-ink-600"><?php echo e($errorCounts['total'] ?? 0); ?></p>
                            <p class="text-xs font-semibold text-ink-700">Total</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6 lg:col-span-2">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-bold text-ink-800">Installations (<?php echo e($customer->installations->count()); ?>)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Version</th>
                                    <th>Status</th>
                                    <th>Domain</th>
                                    <th>Last Heartbeat</th>
                                    <th>Registered</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $customer->installations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $installation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="font-mono text-xs text-ink-600"><?php echo e($installation->app_version ?? '--'); ?></td>
                                        <td><span class="<?php echo e($installStatusColor($installation->status)); ?>"><?php echo e(ucfirst($installation->status)); ?></span></td>
                                        <td class="text-xs text-ink-600"><?php echo e($installation->domain ?? '--'); ?></td>
                                        <td class="text-xs text-ink-400"><?php echo e($installation->last_heartbeat_at?->diffForHumans() ?? '--'); ?></td>
                                        <td class="text-xs text-ink-400"><?php echo e($installation->created_at->format('d M Y')); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="py-6 text-center text-sm text-ink-400">No installations.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3 class="text-sm font-bold text-ink-800">Ticket History (<?php echo e($customer->tickets->count()); ?>)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Created</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $customer->tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ticket): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td class="font-mono text-xs text-ink-500"><?php echo e($ticket->ticket_number); ?></td>
                                        <td class="font-medium text-ink-800"><?php echo e(Str::limit($ticket->subject, 40)); ?></td>
                                        <td><span class="<?php echo e($statusColor($ticket->status)); ?>"><?php echo e(str_replace('_', ' ', ucfirst($ticket->status))); ?></span></td>
                                        <td><span class="<?php echo e(match($ticket->priority) { 'urgent' => 'badge-red', 'high' => 'badge-amber', 'medium' => 'badge-yellow', 'low' => 'badge-green', default => 'badge-gray' }); ?>"><?php echo e(ucfirst($ticket->priority)); ?></span></td>
                                        <td class="text-xs text-ink-400"><?php echo e($ticket->created_at->diffForHumans()); ?></td>
                                        <td class="text-right">
                                            <a href="<?php echo e(route('admin.support.tickets.show', $ticket)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="py-6 text-center text-sm text-ink-400">No tickets.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\control-center\customer.blade.php ENDPATH**/ ?>