<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Finance Dashboard','breadcrumbs' => [['label' => 'Finance'], ['label' => 'Dashboard']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Finance Dashboard','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Finance'], ['label' => 'Dashboard']])]); ?>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <?php if (isset($component)) { $__componentOriginal457ade557f73eaa008f851091260abe1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal457ade557f73eaa008f851091260abe1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Receivables (outstanding)','value' => number_format($totalReceivable, 2),'icon' => 'wallet','color' => 'amber','url' => ''.e(route('finance.receivables')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Receivables (outstanding)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($totalReceivable, 2)),'icon' => 'wallet','color' => 'amber','url' => ''.e(route('finance.receivables')).'']); ?>
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
        <?php if (isset($component)) { $__componentOriginal457ade557f73eaa008f851091260abe1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal457ade557f73eaa008f851091260abe1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Payables (outstanding)','value' => number_format($totalPayable, 2),'icon' => 'receipt','color' => 'red','url' => ''.e(route('finance.payables')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Payables (outstanding)','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($totalPayable, 2)),'icon' => 'receipt','color' => 'red','url' => ''.e(route('finance.payables')).'']); ?>
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
        <?php if (isset($component)) { $__componentOriginal457ade557f73eaa008f851091260abe1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal457ade557f73eaa008f851091260abe1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Cash Received','value' => number_format($cashReceived, 2),'icon' => 'banknotes','color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Cash Received','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($cashReceived, 2)),'icon' => 'banknotes','color' => 'green']); ?>
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
        <?php if (isset($component)) { $__componentOriginal457ade557f73eaa008f851091260abe1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal457ade557f73eaa008f851091260abe1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Landed Costs','value' => number_format($landedCosts, 2),'icon' => 'truck','color' => 'violet']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Landed Costs','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(number_format($landedCosts, 2)),'icon' => 'truck','color' => 'violet']); ?>
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
    </div>

    <div class="grid gap-6 lg:grid-cols-2 mt-6">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900 dark:text-ink-50">Trial Balance</h2>
            <div class="space-y-3">
                <?php $__currentLoopData = [['label' => 'Assets', 'value' => $assets, 'color' => 'text-emerald-600 dark:text-emerald-400'], ['label' => 'Liabilities', 'value' => $liabilities, 'color' => 'text-red-600 dark:text-red-400'], ['label' => 'Equity', 'value' => $equity, 'color' => 'text-ink-800 dark:text-ink-200'], ['label' => 'Income', 'value' => $income, 'color' => 'text-emerald-600 dark:text-emerald-400'], ['label' => 'Expenses', 'value' => $expenses, 'color' => 'text-red-600 dark:text-red-400']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-ink-500 dark:text-ink-400"><?php echo e($row['label']); ?></span>
                        <span class="font-semibold <?php echo e($row['color']); ?>"><?php echo e(number_format((float) $row['value'], 2)); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>

        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900 dark:text-ink-50">Top Accounts</h2>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead><tr><th>Account</th><th>Code</th><th>Entries</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $topAccounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><a href="<?php echo e(route('finance.accounts.show', $account)); ?>" class="font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300"><?php echo e($account->name); ?></a></td>
                                <td><?php echo e($account->code); ?></td>
                                <td><?php echo e($account->lines_count); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="3" class="py-6 text-center text-ink-400 dark:text-ink-500">No accounts yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-6">
        <h2 class="mb-3 text-lg font-bold text-ink-900 dark:text-ink-50">Invoiced Value — Last 6 Months</h2>
        <?php if($monthly->count()): ?>
            <div class="flex items-end gap-3 overflow-x-auto pb-2">
                <?php $__currentLoopData = $monthly; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $max = max(1, (float) $monthly->max()); $pct = ((float) $value / $max) * 100; ?>
                    <div class="flex min-w-[4rem] flex-col items-center gap-1">
                        <span class="text-xs font-semibold text-ink-700 dark:text-ink-300"><?php echo e(number_format((float) $value, 0)); ?></span>
                        <div class="h-28 w-10 rounded-t-lg bg-gradient-to-t from-navy-700 to-navy-400" style="height: <?php echo e($pct); ?>%"></div>
                        <span class="text-xs text-ink-400 dark:text-ink-500"><?php echo e(\Illuminate\Support\Carbon::parse($month . '-01')->format('M y')); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <p class="text-sm text-ink-400 dark:text-ink-500">No invoice data for the last 6 months.</p>
        <?php endif; ?>
    </div>

    <div class="card mt-6">
        <h2 class="mb-3 text-lg font-bold text-ink-900 dark:text-ink-50">Recent Journal Entries</h2>
        <div class="overflow-x-auto">
            <table class="table">
                <thead><tr><th>Entry</th><th>Date</th><th>Description</th><th>Created By</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentEntries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="font-semibold text-ink-800 dark:text-ink-100"><?php echo e($entry->entry_number); ?></td>
                            <td><?php echo e($entry->date?->format('d M Y')); ?></td>
                            <td><?php echo e($entry->description ?? '—'); ?></td>
                            <td><?php echo e($entry->createdBy?->name ?? '—'); ?></td>
                            <td><span class="badge-<?php echo e($entry->status === 'posted' ? 'green' : 'amber'); ?>"><?php echo e(\App\Models\JournalEntry::STATUSES[$entry->status] ?? $entry->status); ?></span></td>
                            <td class="text-right"><a href="<?php echo e(route('finance.journals.show', $entry)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">View</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="py-6 text-center text-ink-400 dark:text-ink-500">No journal entries yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
<?php /**PATH C:\MY_ERP\resources\views\finance\dashboard.blade.php ENDPATH**/ ?>