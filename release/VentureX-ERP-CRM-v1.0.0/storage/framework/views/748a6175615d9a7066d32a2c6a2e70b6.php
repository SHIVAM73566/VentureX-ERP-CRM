<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Customers','breadcrumbs' => [['label' => 'CRM'], ['label' => 'Customers']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Customers','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'CRM'], ['label' => 'Customers']])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Customer::class)): ?>
            <a href="<?php echo e(route('customers.create')); ?>" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Customer
            </a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="<?php echo e(route('customers.index')); ?>" class="flex flex-1 gap-2">
            <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search customers..." class="input max-w-md" />
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <option value="active" <?php if(request('status') === 'active'): echo 'selected'; endif; ?>>Active</option>
                <option value="inactive" <?php if(request('status') === 'inactive'): echo 'selected'; endif; ?>>Inactive</option>
                <option value="prospect" <?php if(request('status') === 'prospect'): echo 'selected'; endif; ?>>Prospect</option>
            </select>
            <button type="submit" class="btn-secondary">Search</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Contact</th>
                        <th>Contacts</th>
                        <th>Opportunities</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-800 dark:text-ink-100"><?php echo e($customer->name); ?></p>
                                <p class="text-xs text-ink-400 dark:text-ink-500"><?php echo e($customer->tax_id ? 'VAT: '.$customer->tax_id : '—'); ?></p>
                            </td>
                            <td class="text-sm text-ink-600 dark:text-ink-400"><?php echo e($customer->email ?? $customer->phone ?? '—'); ?></td>
                            <td><?php echo e($customer->contacts_count); ?></td>
                            <td><?php echo e($customer->opportunities_count); ?></td>
                            <td>
                                <span class="<?php echo e($customer->status === 'active' ? 'badge-green' : 'badge-gray'); ?>"><?php echo e(ucfirst($customer->status)); ?></span>
                            </td>
                            <td class="text-right">
                                <a href="<?php echo e(route('customers.show', $customer)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">View</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="py-8 text-center text-ink-400 dark:text-ink-500">No customers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4"><?php echo e($customers->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views/crm/customers/index.blade.php ENDPATH**/ ?>