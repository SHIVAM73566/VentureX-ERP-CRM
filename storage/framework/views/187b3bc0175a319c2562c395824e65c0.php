<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'AI Document Reader','breadcrumbs' => [['label' => 'AI'], ['label' => 'Document Reader']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'AI Document Reader','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'AI'], ['label' => 'Document Reader']])]); ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Analyze a Document</h2>
                <form method="POST" action="<?php echo e(route('ai.document-reader.analyze')); ?>" x-data="{ submitting: false }" @submit="submitting = true" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <?php if (isset($component)) { $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.select','data' => ['label' => 'Document *','name' => 'document_id','options' => $documents->mapWithKeys(fn ($d) => [$d->id => ($d->original_name ?? ('#'.$d->id))]),'required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.select'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Document *','name' => 'document_id','options' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($documents->mapWithKeys(fn ($d) => [$d->id => ($d->original_name ?? ('#'.$d->id))])),'required' => true]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $attributes = $__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__attributesOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36)): ?>
<?php $component = $__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36; ?>
<?php unset($__componentOriginal8cee41e4af1fe2df52d1d5acd06eed36); ?>
<?php endif; ?>
                    <?php if (isset($component)) { $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input','data' => ['label' => 'Focus (optional)','name' => 'focus','placeholder' => 'e.g. Extract payment terms and price clauses']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Focus (optional)','name' => 'focus','placeholder' => 'e.g. Extract payment terms and price clauses']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $attributes = $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b)): ?>
<?php $component = $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b; ?>
<?php unset($__componentOriginal5c2a97ab476b69c1189ee85d1a95204b); ?>
<?php endif; ?>
                    <label class="flex items-start gap-2 rounded-lg border border-ink-200 p-3 text-sm text-ink-600">
                        <input type="checkbox" name="deep" value="1" class="mt-0.5">
                        <span>
                            <span class="font-semibold text-ink-800">Complex analysis</span>
                            <span class="block text-xs text-ink-400">Deeper reasoning for contracts and technical documents: obligations, payment terms, risks and inconsistencies. Only use when you need it.</span>
                        </span>
                    </label>
                    <button type="submit" class="btn-accent w-full" :disabled="submitting">
                        <template x-if="!submitting"><span>Analyze Document</span></template>
                        <template x-if="submitting"><span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Analyzing…</span></template>
                    </button>
                </form>
                <p class="mt-3 text-xs text-ink-400">Runs on <?php echo e(config('ai.provider')); ?> via <?php echo e(config('ai.model')); ?>. Analyses are saved to audit logs and run history.</p>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <?php $activeRunId = request()->integer('run'); ?>
            <?php $__empty_1 = true; $__currentLoopData = $runs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $run): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="card <?php echo e($run->id === $activeRunId ? 'ring-2 ring-navy-500' : ''); ?>">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-xs text-ink-400"><?php echo e($run->created_at?->format('d M Y H:i')); ?> • <?php echo e($run->model ?? '—'); ?></p>
                            <h3 class="mt-0.5 font-bold text-ink-900"><?php echo e($run->input['document_id'] ?? '' ? 'Document analysis' : 'Analysis'); ?></h3>
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
                <div class="card"><p class="py-8 text-center text-ink-400">No document analyses yet. Select a document and run an analysis.</p></div>
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
<?php /**PATH C:\MY_ERP\resources\views/ai/document-reader.blade.php ENDPATH**/ ?>