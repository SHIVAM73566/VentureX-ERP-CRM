<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => ''.e($opportunity->name).'','breadcrumbs' => [['label' => 'CRM', 'url' => route('opportunities.index')], ['label' => 'Opportunities', 'url' => route('opportunities.index')], ['label' => $opportunity->name]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($opportunity->name).'','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'CRM', 'url' => route('opportunities.index')], ['label' => 'Opportunities', 'url' => route('opportunities.index')], ['label' => $opportunity->name]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\AiRun::class)): ?>
            <?php if (isset($component)) { $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.action','data' => ['label' => 'Review Opportunity','url' => route('ai.deep.opportunity'),'payload' => ['opportunity_id' => $opportunity->id],'intro' => 'A specialist analysis of this deal: why it may close, blockers, missing information, next steps, strongest follow-up and anticipated objections — all for your review.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Review Opportunity','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ai.deep.opportunity')),'payload' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['opportunity_id' => $opportunity->id]),'intro' => 'A specialist analysis of this deal: why it may close, blockers, missing information, next steps, strongest follow-up and anticipated objections — all for your review.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef)): ?>
<?php $attributes = $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef; ?>
<?php unset($__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalac3ae74eec2b8a59d49d7482de1288ef)): ?>
<?php $component = $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef; ?>
<?php unset($__componentOriginalac3ae74eec2b8a59d49d7482de1288ef); ?>
<?php endif; ?>
        <?php endif; ?>
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $opportunity)): ?>
            <a href="<?php echo e(route('opportunities.edit', $opportunity)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card space-y-3">
            <h2 class="text-lg font-bold text-ink-900"><?php echo e($opportunity->name); ?></h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Stage</dt><dd><span class="badge-<?php echo e($opportunity->stage === 'won' ? 'green' : ($opportunity->stage === 'lost' ? 'red' : 'blue')); ?>"><?php echo e(\App\Models\Opportunity::STAGES[$opportunity->stage]); ?></span></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Value</dt><dd><?php echo e(number_format((float) $opportunity->expected_value, 2)); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Probability</dt><dd><?php echo e($opportunity->probability ? round((float) $opportunity->probability, 0).'%' : '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Close Date</dt><dd><?php echo e($opportunity->expected_close_date?->format('d M Y') ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Assigned To</dt><dd><?php echo e($opportunity->assignedTo?->name ?? '—'); ?></dd></div>
                <?php if($opportunity->source): ?><div class="flex justify-between"><dt class="text-ink-400">Source</dt><dd><?php echo e($opportunity->source); ?></dd></div><?php endif; ?>
            </dl>
            <?php if($opportunity->notes): ?>
                <div class="rounded-lg bg-ink-50 p-3 text-sm text-ink-600"><?php echo e($opportunity->notes); ?></div>
            <?php endif; ?>
            <?php if($opportunity->customer): ?>
                <a href="<?php echo e(route('customers.show', $opportunity->customer)); ?>" class="block rounded-lg border border-ink-200 p-3 hover:bg-ink-50">
                    <p class="text-xs text-ink-400">Customer</p>
                    <p class="text-sm font-semibold text-navy-600"><?php echo e($opportunity->customer->name); ?></p>
                </a>
            <?php endif; ?>
        </div>

        <div class="card lg:col-span-2">
            <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Activities</h3>
            <?php $__empty_1 = true; $__currentLoopData = $opportunity->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="border-l-2 border-navy-100 py-1 pl-4">
                    <p class="text-sm text-ink-800"><span class="font-semibold"><?php echo e(ucfirst($activity->type)); ?></span> — <?php echo e($activity->title); ?></p>
                    <p class="text-xs text-ink-400"><?php echo e($activity->due_at?->format('d M Y, H:i')); ?> <?php if($activity->assignedTo): ?> by <?php echo e($activity->assignedTo->name); ?><?php endif; ?></p>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-ink-400">No activities logged.</p>
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
<?php /**PATH C:\MY_ERP\resources\views/crm/opportunities/show.blade.php ENDPATH**/ ?>