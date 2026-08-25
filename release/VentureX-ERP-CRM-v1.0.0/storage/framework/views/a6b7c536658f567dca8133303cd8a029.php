<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Chart of Accounts','breadcrumbs' => [['label' => 'Finance'], ['label' => 'Accounts']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Chart of Accounts','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Finance'], ['label' => 'Accounts']])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('create', App\Models\Account::class)): ?>
            <a href="<?php echo e(route('finance.accounts.create')); ?>" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                New Account
            </a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-5">
        <?php $__currentLoopData = $totals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $colors = ['asset' => 'green', 'liability' => 'red', 'equity' => 'violet', 'income' => 'blue', 'expense' => 'amber']; ?>
            <?php if (isset($component)) { $__componentOriginal457ade557f73eaa008f851091260abe1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal457ade557f73eaa008f851091260abe1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => ucfirst($type).'s','value' => $count,'icon' => 'folder','color' => $colors[$type] ?? 'gray']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(ucfirst($type).'s'),'value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($count),'icon' => 'folder','color' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($colors[$type] ?? 'gray')]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal457ade557f73eaa008f851091260abe1)): ?>
<?php $attributes = $__attributesOriginal457ade557f73eaa008f851091260abe1; ?>
<?php unset($__attributesOriginal457ade557f73eaa008f851091260abe1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal457ade557f73eaa008f851091260abe1)): ?>
<?php $component = $__componentOriginal457ade557f73eaa008f851091260abe1; ?>
<?php unset($__componentOriginal457ade557f73eaa008f851091260abe1); ?>
<?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="<?php echo e(route('finance.accounts.index')); ?>" class="flex flex-1 flex-wrap gap-2">
            <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search name or code..." class="input max-w-md" />
            <select name="type" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All types</option>
                <?php $__currentLoopData = \App\Models\Account::TYPES; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>" <?php if(request('type') === $key): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Parent</th><th>Balance</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-mono text-ink-600"><?php echo e($account->code); ?></td>
                            <td><a href="<?php echo e(route('finance.accounts.show', $account)); ?>" class="font-semibold text-navy-600 hover:text-navy-500"><?php echo e($account->name); ?></a></td>
                            <td><span class="badge-<?php echo e($account->type === 'asset' ? 'green' : ($account->type === 'liability' ? 'red' : ($account->type === 'income' ? 'blue' : 'gray'))); ?>"><?php echo e(\App\Models\Account::TYPES[$account->type] ?? $account->type); ?></span></td>
                            <td><?php echo e($account->parent?->name ?? '—'); ?></td>
                            <td class="font-semibold text-ink-800"><?php echo e(number_format((float) $account->balance(), 2)); ?></td>
                            <td><span class="badge-<?php echo e($account->is_active ? 'green' : 'gray'); ?>"><?php echo e($account->is_active ? 'Active' : 'Inactive'); ?></span></td>
                            <td class="text-right">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $account)): ?>
                                    <a href="<?php echo e(route('finance.accounts.edit', $account)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500">Edit</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="7" class="py-8 text-center text-ink-400">No accounts yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4"><?php echo e($accounts->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views/finance/accounts/index.blade.php ENDPATH**/ ?>