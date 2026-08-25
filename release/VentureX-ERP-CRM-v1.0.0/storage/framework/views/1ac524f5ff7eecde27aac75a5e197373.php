<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Payment — '.($payment->payment_number ?? 'New'),'breadcrumbs' => [['label' => 'Sales', 'url' => route('sales.payments.index')], ['label' => 'Payments', 'url' => route('sales.payments.index')], ['label' => $payment->payment_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Payment — '.($payment->payment_number ?? 'New')),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Sales', 'url' => route('sales.payments.index')], ['label' => 'Payments', 'url' => route('sales.payments.index')], ['label' => $payment->payment_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $payment)): ?>
            <a href="<?php echo e(route('sales.payments.edit', $payment)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('delete', $payment)): ?>
            <form method="POST" action="<?php echo e(route('sales.payments.destroy', $payment)); ?>" onsubmit="return confirm('Delete this payment? It will be reversed from the invoice.')">
                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                <button type="submit" class="btn-secondary text-red-600 hover:bg-red-50">Delete</button>
            </form>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="mx-auto max-w-2xl">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Payment Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Number</dt><dd><?php echo e($payment->payment_number); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Customer</dt><dd><?php echo e($payment->customer?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Invoice</dt>
                    <dd><?php if($payment->invoice): ?><a href="<?php echo e(route('sales.invoices.show', $payment->invoice)); ?>" class="text-navy-600 hover:text-navy-500"><?php echo e($payment->invoice->invoice_number); ?></a><?php else: ?> — <?php endif; ?></dd>
                </div>
                <div class="flex justify-between"><dt class="text-ink-400">Date</dt><dd><?php echo e($payment->payment_date?->format('d M Y')); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Method</dt><dd><?php echo e(\App\Models\Payment::METHODS[$payment->method] ?? $payment->method); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Reference</dt><dd><?php echo e($payment->reference ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt>
                    <dd><?php $c = ['pending' => 'amber', 'completed' => 'green', 'failed' => 'red', 'refunded' => 'gray']; ?>
                        <span class="badge-<?php echo e($c[$payment->status] ?? 'gray'); ?>"><?php echo e(\App\Models\Payment::STATUSES[$payment->status] ?? $payment->status); ?></span>
                    </dd>
                </div>
                <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Amount</dt><dd class="font-bold text-ink-900"><?php echo e(number_format((float) $payment->amount, 2)); ?></dd></div>
            </dl>

            <?php if($payment->notes): ?>
                <div class="mt-4 border-t border-ink-100 pt-3"><h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">Notes</h3><p class="mt-1 text-sm whitespace-pre-line text-ink-700"><?php echo e($payment->notes); ?></p></div>
            <?php endif; ?>
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
<?php /**PATH C:\MY_ERP\resources\views/sales/payments/show.blade.php ENDPATH**/ ?>