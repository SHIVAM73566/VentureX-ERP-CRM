<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Customer Management','breadcrumbs' => [['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Customers']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Customer Management'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Administration', 'url' => route('admin.control-center.index')], ['label' => 'Control Center', 'url' => route('admin.control-center.index')], ['label' => 'Customers']])]); ?>

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
    ?>

    <div class="mx-auto max-w-7xl space-y-6">

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800">Customers</h2>
                <form method="GET" action="<?php echo e(route('admin.control-center.customers')); ?>" class="flex items-center gap-2">
                    <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search company..." class="input max-w-xs">
                    <select name="license_status" class="input max-w-[10rem]" onchange="this.form.submit()">
                        <option value="">All statuses</option>
                        <?php $__currentLoopData = ['active', 'trial', 'expired', 'suspended']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($s); ?>" <?php if(request('license_status') === $s): echo 'selected'; endif; ?>><?php echo e(ucfirst($s)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <select name="license_tier" class="input max-w-[10rem]" onchange="this.form.submit()">
                        <option value="">All tiers</option>
                        <?php $__currentLoopData = ['enterprise', 'professional', 'starter']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($t); ?>" <?php if(request('license_tier') === $t): echo 'selected'; endif; ?>><?php echo e(ucfirst($t)); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="submit" class="btn-secondary">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>License Tier</th>
                            <th>License Status</th>
                            <th>Installations</th>
                            <th>Tickets</th>
                            <th>Errors</th>
                            <th>Last Active</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="font-medium text-ink-800"><?php echo e($customer->name); ?></td>
                                <td><span class="<?php echo e($licenseTierColor($customer->license?->tier)); ?>"><?php echo e(ucfirst($customer->license?->tier ?? '--')); ?></span></td>
                                <td><span class="<?php echo e($licenseStatusColor($customer->license?->status)); ?>"><?php echo e(ucfirst($customer->license?->status ?? '--')); ?></span></td>
                                <td class="text-center text-ink-600"><?php echo e($customer->installations_count ?? $customer->installations->count() ?? 0); ?></td>
                                <td class="text-center text-ink-600"><?php echo e($customer->tickets_count ?? 0); ?></td>
                                <td class="text-center text-ink-600"><?php echo e($customer->errors_count ?? 0); ?></td>
                                <td class="text-xs text-ink-400"><?php echo e($customer->last_active_at?->diffForHumans() ?? '--'); ?></td>
                                <td class="text-right">
                                    <a href="<?php echo e(route('admin.control-center.customer', $customer)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="8" class="py-8 text-center text-sm text-ink-400">No customers found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-4"><?php echo e($customers->withQueryString()->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\control-center\customers.blade.php ENDPATH**/ ?>