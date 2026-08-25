<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Suppliers','breadcrumbs' => [['label' => 'Procurement'], ['label' => 'Suppliers']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Suppliers','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Procurement'], ['label' => 'Suppliers']])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Supplier::class)): ?>
            <a href="<?php echo e(route('suppliers.create')); ?>" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Supplier
            </a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="<?php echo e(route('suppliers.index')); ?>" class="flex flex-1 gap-2">
            <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search suppliers..." class="input max-w-md" />
            <select name="status" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                <?php $__currentLoopData = \App\Models\Supplier::STATUSES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $status): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php if(request('status') === $key): echo 'selected'; endif; ?>><?php echo e($status); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="btn-secondary">Search</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Supplier</th>
                        <th>Contact</th>
                        <th>Offers</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-800"><?php echo e($supplier->name); ?></p>
                                <p class="text-xs text-ink-400"><?php echo e($supplier->city ?? '—'); ?><?php echo e($supplier->country ? ', '.$supplier->country->name : ''); ?></p>
                            </td>
                            <td class="text-sm text-ink-600"><?php echo e($supplier->email ?? $supplier->phone ?? '—'); ?></td>
                            <td><?php echo e($supplier->offers_count); ?></td>
                            <td>
                                <span class="badge-<?php echo e($supplier->status === 'approved' ? 'green' : ($supplier->status === 'blocked' || $supplier->status === 'rejected' ? 'red' : 'amber')); ?>"><?php echo e(\App\Models\Supplier::STATUSES[$supplier->status]); ?></span>
                            </td>
                            <td class="text-right">
                                <a href="<?php echo e(route('suppliers.show', $supplier)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="py-8 text-center text-ink-400">No suppliers found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4"><?php echo e($suppliers->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views/procurement/suppliers/index.blade.php ENDPATH**/ ?>