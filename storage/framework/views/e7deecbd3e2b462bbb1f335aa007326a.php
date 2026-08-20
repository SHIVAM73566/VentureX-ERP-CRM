<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Payment Successful']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Payment Successful']); ?>
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="max-w-md w-full text-center">
            <div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200 dark:border-ink-800 p-8 shadow-lg">
                
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900/30">
                    <svg class="h-10 w-10 text-emerald-600 dark:text-emerald-400 animate-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-ink-900 dark:text-ink-50 mb-3">Payment Successful!</h1>
                <p class="text-ink-500 dark:text-ink-400 mb-2">Your payment has been processed and confirmed.</p>
                <p class="text-ink-400 dark:text-ink-500 text-sm mb-8">A receipt has been sent to your email address.</p>

                
                <div class="rounded-lg bg-ink-50 dark:bg-ink-800/50 border border-ink-200 dark:border-ink-700 p-4 mb-6 text-left">
                    <h3 class="text-sm font-semibold text-ink-700 dark:text-ink-200 mb-2">Next Steps</h3>
                    <ol class="space-y-2 text-sm text-ink-600 dark:text-ink-300 list-decimal list-inside">
                        <li>Check your email for the license key and download link.</li>
                        <li>Download the application archive from the link provided.</li>
                        <li>Extract and run the installation wizard.</li>
                        <li>Enter your license key when prompted during setup.</li>
                    </ol>
                </div>

                <?php if(!empty($reference)): ?>
                    <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 p-3 mb-6">
                        <p class="text-sm text-blue-700 dark:text-blue-300">
                            <span class="font-semibold">Reference:</span>
                            <code class="font-mono"><?php echo e($reference); ?></code>
                        </p>
                    </div>
                <?php endif; ?>

                <div class="space-y-3">
                    <a href="<?php echo e(route('support.docs')); ?>" class="block w-full rounded-lg bg-blue-600 hover:bg-blue-700 px-4 py-3 text-sm font-semibold text-white transition">
                        View Documentation
                    </a>
                    <a href="<?php echo e(url('/')); ?>" class="block w-full rounded-lg border border-ink-300 dark:border-ink-600 bg-white dark:bg-ink-800 px-4 py-3 text-sm font-medium text-ink-700 dark:text-ink-200 hover:bg-ink-50 dark:hover:bg-ink-700 transition">
                        Return to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('styles'); ?>
    <style>
        @keyframes checkDraw {
            0% { stroke-dashoffset: 24; opacity: 0; }
            50% { opacity: 1; }
            100% { stroke-dashoffset: 0; opacity: 1; }
        }
        .animate-check {
            stroke-dasharray: 24;
            animation: checkDraw 0.6s ease-out 0.2s forwards;
            opacity: 0;
        }
    </style>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH C:\MY_ERP\resources\views/pricing/success.blade.php ENDPATH**/ ?>