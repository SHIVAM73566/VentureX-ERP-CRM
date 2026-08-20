<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Procurement AI','breadcrumbs' => [['label' => 'AI'], ['label' => 'Procurement Intelligence']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Procurement AI','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'AI'], ['label' => 'Procurement Intelligence']])]); ?>

    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 300)">
        <div x-show="loading" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <?php for($i = 0; $i < 4; $i++): ?>
                <?php if (isset($component)) { $__componentOriginal59c8251351468c2ad0d4137d183b216f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal59c8251351468c2ad0d4137d183b216f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ui.skeleton-stat','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ui.skeleton-stat'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal59c8251351468c2ad0d4137d183b216f)): ?>
<?php $attributes = $__attributesOriginal59c8251351468c2ad0d4137d183b216f; ?>
<?php unset($__attributesOriginal59c8251351468c2ad0d4137d183b216f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal59c8251351468c2ad0d4137d183b216f)): ?>
<?php $component = $__componentOriginal59c8251351468c2ad0d4137d183b216f; ?>
<?php unset($__componentOriginal59c8251351468c2ad0d4137d183b216f); ?>
<?php endif; ?>
            <?php endfor; ?>
        </div>
        <div x-show="!loading" x-cloak class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <?php if (isset($component)) { $__componentOriginal457ade557f73eaa008f851091260abe1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal457ade557f73eaa008f851091260abe1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Supplier Offers','value' => $summary['offers'],'icon' => 'document','color' => 'blue']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Supplier Offers','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['offers']),'icon' => 'document','color' => 'blue']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Open Requisitions','value' => $summary['open_requisitions'],'icon' => 'alert','color' => 'amber']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Open Requisitions','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['open_requisitions']),'icon' => 'alert','color' => 'amber']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Suppliers','value' => $summary['suppliers'],'icon' => 'users','color' => 'green']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Suppliers','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['suppliers']),'icon' => 'users','color' => 'green']); ?>
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
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Red-Flag Offers','value' => $summary['red_offers'],'icon' => 'flag','color' => 'red']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Red-Flag Offers','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($summary['red_offers']),'icon' => 'flag','color' => 'red']); ?>
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
    </div>

    <div class="grid gap-6 lg:grid-cols-3 mt-6">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Ask Procurement Intelligence</h2>
            <form method="POST" action="<?php echo e(route('ai.procurement.analyze')); ?>" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4">
                <?php echo csrf_field(); ?>
                <?php if (isset($component)) { $__componentOriginalcd97a59301ba78d56b3ed60dd41409ab = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalcd97a59301ba78d56b3ed60dd41409ab = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.textarea','data' => ['label' => 'Question *','name' => 'question','rows' => '5','required' => true,'placeholder' => 'e.g. Compare the best price per MT for HRC grade A36 among the latest offers, and flag any high-risk suppliers.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.textarea'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Question *','name' => 'question','rows' => '5','required' => true,'placeholder' => 'e.g. Compare the best price per MT for HRC grade A36 among the latest offers, and flag any high-risk suppliers.']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginalcd97a59301ba78d56b3ed60dd41409ab)): ?>
<?php $attributes = $__attributesOriginalcd97a59301ba78d56b3ed60dd41409ab; ?>
<?php unset($__attributesOriginalcd97a59301ba78d56b3ed60dd41409ab); ?>
<?php endif; ?>
<?php if (isset($__componentOriginalcd97a59301ba78d56b3ed60dd41409ab)): ?>
<?php $component = $__componentOriginalcd97a59301ba78d56b3ed60dd41409ab; ?>
<?php unset($__componentOriginalcd97a59301ba78d56b3ed60dd41409ab); ?>
<?php endif; ?>
                <button type="submit" class="btn-accent w-full" :disabled="submitting">
                    <template x-if="!submitting"><span>Analyze</span></template>
                    <template x-if="submitting"><span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Analyzing…</span></template>
                </button>
            </form>
            <div class="mt-4 flex items-center justify-between gap-3">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wide text-ink-400">Deep analysis</h3>
                    <p class="mt-1 text-xs text-ink-500">Specialist best-value supplier comparison with risks, negotiation opportunities and alternatives.</p>
                </div>
                <?php if (isset($component)) { $__componentOriginalac3ae74eec2b8a59d49d7482de1288ef = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginalac3ae74eec2b8a59d49d7482de1288ef = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.ai.action','data' => ['label' => 'Deep Procurement Analysis','url' => route('ai.deep.procurement'),'intro' => 'Comparing the latest supplier offers across materials: best-value supplier, reasons, risks, negotiation opportunities and alternatives — for your review. A purchase order is only created after you review and confirm the recommendation.']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('ai.action'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Deep Procurement Analysis','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('ai.deep.procurement')),'intro' => 'Comparing the latest supplier offers across materials: best-value supplier, reasons, risks, negotiation opportunities and alternatives — for your review. A purchase order is only created after you review and confirm the recommendation.']); ?>
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
            </div>
            <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-xs text-amber-800">
                Recommendations never create a purchase order. Review the recommendation, then confirm before creating a PO.
            </div>
            <div class="mt-4">
                <h3 class="text-xs font-bold uppercase tracking-wide text-ink-400">Example questions</h3>
                <ul class="mt-2 space-y-1 text-xs text-ink-500">
                    <li>• "Which supplier has the best offer for TMT bars?"</li>
                    <li>• "Summarize open requisitions and suggested suppliers."</li>
                    <li>• "Any red-flag offers with poor quality status?"</li>
                </ul>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <?php $activeRunId = request()->integer('run'); ?>
            <?php $__empty_1 = true; $__currentLoopData = $runs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $run): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="card <?php echo e($run->id === $activeRunId ? 'ring-2 ring-navy-500' : ''); ?>">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-ink-400"><?php echo e($run->created_at?->format('d M Y H:i')); ?> • <?php echo e($run->model ?? '—'); ?></p>
                            <h3 class="mt-0.5 font-bold text-ink-900">Q: <?php echo e($run->input['question'] ?? 'Procurement analysis'); ?></h3>
                        </div>
                        <?php if($run->status === 'completed'): ?>
                            <span class="badge-green">Completed</span>
                        <?php elseif($run->status === 'running'): ?>
                            <span class="badge-amber">Running</span>
                        <?php else: ?>
                            <span class="badge-red">Failed</span>
                        <?php endif; ?>
                    </div>

                    <?php if($run->status === 'completed' && isset($run->output['content'])): ?>
                        <div class="mt-3 rounded-lg bg-ink-50 p-4">
                            <p class="whitespace-pre-wrap text-sm leading-relaxed text-ink-800"><?php echo e($run->output['content']); ?></p>
                        </div>
                        <p class="mt-2 text-xs text-ink-400"><?php echo e($run->prompt_tokens ?? 0); ?> prompt tokens • <?php echo e($run->completion_tokens ?? 0); ?> completion tokens</p>
                    <?php elseif($run->status === 'failed'): ?>
                        <p class="mt-3 rounded-lg bg-red-50 p-3 text-sm text-red-700"><?php echo e($run->error['message'] ?? 'Analysis failed.'); ?></p>
                    <?php else: ?>
                        <p class="mt-3 text-sm text-ink-400">Analysis in progress…</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="card"><p class="py-8 text-center text-ink-400">No procurement analyses yet. Ask your first question.</p></div>
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
<?php /**PATH C:\MY_ERP\resources\views\ai\procurement.blade.php ENDPATH**/ ?>