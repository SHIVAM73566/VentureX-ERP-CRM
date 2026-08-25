<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => ''.e($lead->contact_name).'','breadcrumbs' => [['label' => 'CRM', 'url' => route('leads.index')], ['label' => 'Leads', 'url' => route('leads.index')], ['label' => $lead->lead_number]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => ''.e($lead->contact_name).'','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'CRM', 'url' => route('leads.index')], ['label' => 'Leads', 'url' => route('leads.index')], ['label' => $lead->lead_number]])]); ?>

     <?php $__env->slot('actions', null, []); ?> 
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('viewAny', App\Models\AiRun::class)): ?>
            <?php if (isset($component)) { $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.action','data' => ['label' => 'AI email draft','url' => route('ai.actions.lead-email'),'payload' => ['lead_id' => $lead->id],'intro' => 'AI will draft a professional follow-up email based on this lead. Review it before sending.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'AI email draft','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ai.actions.lead-email')),'payload' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(['lead_id' => $lead->id]),'intro' => 'AI will draft a professional follow-up email based on this lead. Review it before sending.']); ?>
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
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('update', $lead)): ?>
            <a href="<?php echo e(route('leads.edit', $lead)); ?>" class="btn-secondary">Edit</a>
        <?php endif; ?>
     <?php $__env->endSlot(); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card space-y-3">
            <div>
                <h2 class="text-lg font-bold text-ink-900"><?php echo e($lead->contact_name); ?></h2>
                <p class="text-sm text-ink-400"><?php echo e($lead->lead_number); ?><?php if($lead->company_name): ?> • <?php echo e($lead->company_name); ?><?php endif; ?></p>
            </div>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Status</dt><dd><span class="badge-<?php echo e($lead->status === 'won' ? 'green' : ($lead->status === 'lost' ? 'red' : ($lead->status === 'new' ? 'amber' : 'blue'))); ?>"><?php echo e(\App\Models\Lead::STATUSES[$lead->status]); ?></span></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Source</dt><dd><?php echo e($lead->source ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Score</dt><dd><?php echo e($lead->score ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Value</dt><dd><?php echo e($lead->estimated_value ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Assigned To</dt><dd><?php echo e($lead->assignedTo?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Next Follow-up</dt><dd><?php echo e($lead->next_follow_up?->format('d M Y') ?? '—'); ?></dd></div>
                <?php if($lead->email): ?><div class="flex justify-between"><dt class="text-ink-400">Email</dt><dd><?php echo e($lead->email); ?></dd></div><?php endif; ?>
                <?php if($lead->phone): ?><div class="flex justify-between"><dt class="text-ink-400">Phone</dt><dd><?php echo e($lead->phone); ?></dd></div><?php endif; ?>
                <?php if($lead->website): ?><div class="flex justify-between"><dt class="text-ink-400">Website</dt><dd><?php echo e($lead->website); ?></dd></div><?php endif; ?>
            </dl>
            <?php if($lead->notes): ?>
                <div class="rounded-lg bg-ink-50 p-3 text-sm text-ink-600"><?php echo e($lead->notes); ?></div>
            <?php endif; ?>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Activities</h3>
                <?php $__empty_1 = true; $__currentLoopData = $lead->activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="border-l-2 border-navy-100 py-1 pl-4">
                        <p class="text-sm text-ink-800"><span class="font-semibold"><?php echo e(ucfirst($activity->type)); ?></span> — <?php echo e($activity->title); ?></p>
                        <p class="text-xs text-ink-400"><?php echo e($activity->due_at?->format('d M Y, H:i') ?? '—'); ?></p>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-ink-400">No activities logged.</p>
                <?php endif; ?>
            </div>

            <?php if($lead->opportunity): ?>
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Related Opportunity</h3>
                    <a href="<?php echo e(route('opportunities.show', $lead->opportunity)); ?>" class="font-medium text-navy-600 hover:text-navy-500"><?php echo e($lead->opportunity->name); ?></a>
                </div>
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
<?php /**PATH C:\MY_ERP\resources\views/crm/leads/show.blade.php ENDPATH**/ ?>