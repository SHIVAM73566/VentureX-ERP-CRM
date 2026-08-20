<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'AI Skills','breadcrumbs' => [['label' => 'Admin'], ['label' => 'AI Skills']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'AI Skills','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Admin'], ['label' => 'AI Skills']])]); ?>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="<?php echo e(route('admin.ai-skills.index')); ?>" class="flex flex-1">
            <input type="search" name="q" value="<?php echo e(request('q')); ?>" placeholder="Search name or slug..." class="input max-w-md" />
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <?php $__empty_1 = true; $__currentLoopData = $skills; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="card flex flex-col">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="font-bold text-ink-900"><?php echo e($skill->name); ?></h3>
                        <p class="text-xs font-mono text-ink-400"><?php echo e($skill->slug); ?></p>
                    </div>
                    <span class="badge-<?php echo e($skill->is_active ? 'green' : 'gray'); ?>"><?php echo e($skill->is_active ? 'Active' : 'Inactive'); ?></span>
                </div>
                <p class="mt-2 line-clamp-2 flex-1 text-sm text-ink-500"><?php echo e($skill->description ?? 'No description.'); ?></p>
                <dl class="mt-3 grid grid-cols-3 gap-2 border-t border-ink-100 pt-3 text-center text-xs">
                    <div><dt class="text-ink-400">Provider</dt><dd class="font-semibold text-ink-700"><?php echo e($skill->provider ?? '—'); ?></dd></div>
                    <div><dt class="text-ink-400">Temp</dt><dd class="font-semibold text-ink-700"><?php echo e($skill->temperature ?? '—'); ?></dd></div>
                    <div><dt class="text-ink-400">Max Tokens</dt><dd class="font-semibold text-ink-700"><?php echo e($skill->max_tokens ?? '—'); ?></dd></div>
                </dl>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $skill)): ?>
                    <div class="mt-3 text-right">
                        <a href="<?php echo e(route('admin.ai-skills.edit', $skill)); ?>" class="text-sm font-medium text-navy-600 hover:text-navy-500">Configure</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="card sm:col-span-2 lg:col-span-3"><p class="py-8 text-center text-ink-400">No AI skills configured.</p></div>
        <?php endif; ?>
    </div>

    <div class="mt-4"><?php echo e($skills->links()); ?></div>
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
<?php /**PATH C:\MY_ERP\resources\views/admin/ai-skills/index.blade.php ENDPATH**/ ?>