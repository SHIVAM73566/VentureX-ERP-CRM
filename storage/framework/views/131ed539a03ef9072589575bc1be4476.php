<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Journal Entry — '.($entry->entry_number ?? 'New'),'breadcrumbs' => [['label' => 'Finance', 'url' => route('finance.journals.index')], ['label' => 'Journal Entries', 'url' => route('finance.journals.index')], ['label' => $entry->entry_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Journal Entry — '.($entry->entry_number ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Finance', 'url' => route('finance.journals.index')], ['label' => 'Journal Entries', 'url' => route('finance.journals.index')], ['label' => $entry->entry_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $entry)): ?>
            <?php if($entry->status !== 'posted'): ?>
                <form method="POST" action="<?php echo e(route('finance.journals.post', $entry)); ?>" onsubmit="return confirm('Post this journal entry? This cannot be undone.')">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-accent">Post Entry</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $entry)): ?>
            <?php if($entry->status !== 'posted'): ?>
                <form method="POST" action="<?php echo e(route('finance.journals.destroy', $entry)); ?>" onsubmit="return confirm('Delete this journal entry?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
                </form>
            <?php endif; ?>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Entry Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd><?php echo e($entry->entry_number); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Date</dt><dd><?php echo e($entry->date?->format('d M Y')); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                    <dd><span class="badge-<?php echo e($entry->status === 'posted' ? 'green' : 'amber'); ?>"><?php echo e(\App\Models\JournalEntry::STATUSES[$entry->status] ?? $entry->status); ?></span></dd>
                </div>
                <div class="flex justify-between"><dt class="text-ink-400">Created By</dt><dd><?php echo e($entry->createdBy?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Lines</dt><dd class="font-bold text-ink-900"><?php echo e($entry->lines->count()); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Total Debits</dt><dd class="font-semibold text-emerald-600"><?php echo e(number_format((float) $entry->lines->sum('debit'), 2)); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Total Credits</dt><dd class="font-semibold text-red-600"><?php echo e(number_format((float) $entry->lines->sum('credit'), 2)); ?></dd></div>
            </dl>
        </div>

        <div class="card lg:col-span-2">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Lines</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Account</th><th>Description</th><th class="text-right">Debit</th><th class="text-right">Credit</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $entry->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td>
                                    <a href="<?php echo e(route('finance.accounts.show', $line->account)); ?>" class="font-semibold text-navy-600 hover:text-navy-500"><?php echo e($line->account?->code); ?> — <?php echo e($line->account?->name); ?></a>
                                </td>
                                <td><?php echo e($line->description ?? '—'); ?></td>
                                <td class="text-right text-emerald-600"><?php echo e($line->debit > 0 ? number_format((float) $line->debit, 2) : '—'); ?></td>
                                <td class="text-right text-red-600"><?php echo e($line->credit > 0 ? number_format((float) $line->credit, 2) : '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="py-6 text-center text-ink-400">No lines.</td></tr>
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
<?php /**PATH C:\MY_ERP\resources\views\finance\journals\show.blade.php ENDPATH**/ ?>