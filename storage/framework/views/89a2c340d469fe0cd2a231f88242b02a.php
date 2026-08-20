<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Opportunities','breadcrumbs' => [['label' => 'CRM'], ['label' => 'Opportunities']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Opportunities','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'CRM'], ['label' => 'Opportunities']])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Opportunity::class)): ?>
            <a href="<?php echo e(route('opportunities.create')); ?>" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Opportunity
            </a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="<?php echo e(route('opportunities.index')); ?>" class="flex flex-1 gap-2">
            <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search opportunities..." class="input max-w-md" />
            <select name="stage" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All stages</option>
                <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php if(request('stage') === $key): echo 'selected'; endif; ?>><?php echo e($stage); ?></option>
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
                        <th>Opportunity</th>
                        <th>Customer</th>
                        <th>Stage</th>
                        <th>Value</th>
                        <th>Probability</th>
                        <th>Close</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $opportunities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opportunity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-semibold text-ink-800"><?php echo e($opportunity->name); ?></td>
                            <td><?php echo e($opportunity->customer?->name ?? '—'); ?></td>
                            <td>
                                <span class="badge-<?php echo e($opportunity->stage === 'won' ? 'green' : ($opportunity->stage === 'lost' ? 'red' : 'blue')); ?>"><?php echo e($stages[$opportunity->stage]); ?></span>
                            </td>
                            <td><?php echo e(number_format((float) $opportunity->expected_value, 2)); ?></td>
                            <td><?php echo e($opportunity->probability ? round((float) $opportunity->probability, 0).'%' : '—'); ?></td>
                            <td><?php echo e($opportunity->expected_close_date?->format('d M Y') ?? '—'); ?></td>
                            <td class="text-right">
                                <a href="<?php echo e(route('opportunities.show', $opportunity)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No opportunities found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4"><?php echo e($opportunities->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views\crm\opportunities\index.blade.php ENDPATH**/ ?>