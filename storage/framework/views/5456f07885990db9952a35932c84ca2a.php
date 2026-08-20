<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Account — '.($account->name ?? 'New'),'breadcrumbs' => [['label' => 'Finance', 'url' => route('finance.accounts.index')], ['label' => 'Accounts', 'url' => route('finance.accounts.index')], ['label' => $account->name]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Account — '.($account->name ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Finance', 'url' => route('finance.accounts.index')], ['label' => 'Accounts', 'url' => route('finance.accounts.index')], ['label' => $account->name]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $account)): ?>
            <a href="<?php echo e(route('finance.accounts.edit', $account)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $account)): ?>
            <form method="POST" action="<?php echo e(route('finance.accounts.destroy', $account)); ?>" onsubmit="return confirm('Delete this account?')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Account Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Code</dt><dd class="font-mono"><?php echo e($account->code); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Name</dt><dd><?php echo e($account->name); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Type</dt><dd><span class="badge-<?php echo e($account->type === 'asset' ? 'green' : ($account->type === 'liability' ? 'red' : ($account->type === 'income' ? 'blue' : 'gray'))); ?>"><?php echo e(\App\Models\Account::TYPES[$account->type] ?? $account->type); ?></span></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Parent</dt><dd><?php echo e($account->parent?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="badge-<?php echo e($account->is_active ? 'green' : 'gray'); ?>"><?php echo e($account->is_active ? 'Active' : 'Inactive'); ?></span></dd></div>
                <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Balance</dt><dd class="font-bold text-ink-900"><?php echo e(number_format((float) $account->balance(), 2)); ?></dd></div>
            </dl>

            <?php if($account->description): ?>
                <div class="mt-4 border-t border-ink-100 pt-3"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Description</h3><p class="mt-1 text-sm text-ink-700"><?php echo e($account->description); ?></p></div>
            <?php endif; ?>

            <?php if($account->children->count()): ?>
                <div class="mt-4 border-t border-ink-100 pt-3">
                    <h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Child Accounts</h3>
                    <ul class="mt-2 space-y-1 text-sm">
                        <?php $__currentLoopData = $account->children; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><a href="<?php echo e(route('finance.accounts.show', $child)); ?>" class="text-navy-600 hover:text-navy-500"><?php echo e($child->code); ?> — <?php echo e($child->name); ?></a></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
        </div>

        <div class="card lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Journal Lines</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Entry</th><th>Date</th><th>Description</th><th class="text-right">Debit</th><th class="text-right">Credit</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $account->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><a href="<?php echo e(route('finance.journals.show', $line->journalEntry)); ?>" class="font-medium text-navy-600 hover:text-navy-500"><?php echo e($line->journalEntry->entry_number); ?></a></td>
                                <td><?php echo e($line->journalEntry->date?->format('d M Y')); ?></td>
                                <td><?php echo e($line->description ?? $line->journalEntry->description ?? '—'); ?></td>
                                <td class="text-right text-emerald-600"><?php echo e($line->debit > 0 ? number_format((float) $line->debit, 2) : '—'); ?></td>
                                <td class="text-right text-red-600"><?php echo e($line->credit > 0 ? number_format((float) $line->credit, 2) : '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="py-6 text-center text-ink-400">No journal lines for this account.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
<?php /**PATH C:\MY_ERP\resources\views\finance\accounts\show.blade.php ENDPATH**/ ?>