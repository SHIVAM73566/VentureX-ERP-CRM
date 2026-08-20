<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'AI Usage Plan','breadcrumbs' => [['label' => 'AI Center'], ['label' => 'Usage & Plans']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'AI Usage Plan','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'AI Center'], ['label' => 'Usage & Plans']])]); ?>

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-ink-900">AI Usage & Plans</h1>
        <p class="mt-1 text-sm text-ink-500">Manage your AI usage and choose the plan that fits your business.</p>
    </div>

    
    <div class="card p-6 mb-8">
        <h2 class="text-lg font-semibold text-ink-900 mb-4">Current Usage</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-ink-700">Daily Usage</span>
                    <span class="text-sm text-ink-400"><?php echo e($usage['daily_used'] ?? 0); ?> / <?php echo e($usage['daily_limit'] ?? 1); ?></span>
                </div>
                <div class="w-full bg-ink-100 rounded-full h-2.5">
                    <?php $pct = ($usage['daily_limit'] ?? 1) > 0 ? min(100, (($usage['daily_used'] ?? 0) / ($usage['daily_limit'] ?? 1)) * 100) : 0; ?>
                    <div class="h-2.5 rounded-full <?php echo e($pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-navy-600')); ?>" style="width: <?php echo e($pct); ?>%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-ink-700">Weekly Usage</span>
                    <span class="text-sm text-ink-400"><?php echo e($usage['weekly_used'] ?? 0); ?> / <?php echo e($usage['weekly_limit'] ?? 3); ?></span>
                </div>
                <div class="w-full bg-ink-100 rounded-full h-2.5">
                    <?php $pct = ($usage['weekly_limit'] ?? 3) > 0 ? min(100, (($usage['weekly_used'] ?? 0) / ($usage['weekly_limit'] ?? 3)) * 100) : 0; ?>
                    <div class="h-2.5 rounded-full <?php echo e($pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-navy-600')); ?>" style="width: <?php echo e($pct); ?>%"></div>
                </div>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="card p-6 border-2 <?php echo e($key === 'free' ? 'border-navy-500' : 'border-transparent'); ?> hover:border-navy-300 transition">
            <?php if($key === 'free'): ?>
                <span class="inline-block bg-navy-100 text-navy-800 text-xs font-medium px-2.5 py-0.5 rounded mb-3">Current Plan</span>
            <?php endif; ?>
            <h3 class="text-lg font-semibold text-ink-900"><?php echo e($plan['name']); ?></h3>
            <div class="mt-2 mb-4">
                <span class="text-3xl font-bold text-ink-900">$<?php echo e($plan['price']); ?></span>
                <span class="text-sm text-ink-400">/<?php echo e($plan['period']); ?></span>
            </div>
            <ul class="space-y-2 mb-6">
                <?php $__currentLoopData = $plan['features']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <li class="flex items-start text-sm text-ink-600">
                    <svg class="w-4 h-4 text-emerald-500 mr-1.5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    <?php echo e($feature); ?>

                </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
            <div class="text-sm text-ink-400 mb-4">
                <?php echo e($plan['daily_limit']); ?> queries/day &middot; <?php echo e($plan['weekly_limit']); ?>/week
            </div>
            <?php if($key !== 'free'): ?>
            <button class="w-full bg-navy-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-navy-700 transition">
                Contact Support for Upgrade
            </button>
            <?php else: ?>
            <button class="w-full bg-ink-100 text-ink-400 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                Current Plan
            </button>
            <?php endif; ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="mt-8 bg-ink-50 rounded-lg p-6 text-center">
        <p class="text-sm text-ink-500">Need a custom plan or have questions?</p>
        <p class="mt-1 text-sm font-medium text-ink-900">Contact support for upgrade options.</p>
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
<?php /**PATH C:\MY_ERP\resources\views/ai/usage-plan.blade.php ENDPATH**/ ?>