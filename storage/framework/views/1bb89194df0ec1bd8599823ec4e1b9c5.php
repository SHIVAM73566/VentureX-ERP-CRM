<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Dashboard','breadcrumbs' => [['label' => 'Dashboard']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Dashboard','breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Dashboard']])]); ?>

    <?php
        $user = auth()->user();
        $icons = [
            'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
            'target' => 'M12 2a10 10 0 100 20 10 10 0 000-20zM12 6a6 6 0 100 12 6 6 0 000-12zm0 4a2 2 0 100 4 2 2 0 000-4z',
            'trending' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
            'wallet' => 'M3 10h18M7 15h2m-5 4h16a1 1 0 001-1V6a1 1 0 00-1-1H4a1 1 0 00-1 1v12a1 1 0 001 1z',
            'chart' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
            'cart' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
            'box' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'ship' => 'M3 13l9 5 9-5M3 13l9-5 9 5M3 13v5l9 5 9-5v-5M12 3v5m0 0l2 1.5M12 8l-2 1.5',
            'folder' => 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z',
            'spark' => 'M5 3l.8 2.2L8 6l-2.2.8L5 9l-.8-2.2L2 6l2.2-.8L5 3zm11 0l.8 2.2L19 6l-2.2.8L16 9l-.8-2.2L13 6l2.2-.8L16 3zm-5 5l.8 2.2L14 11l-2.2.8L11 14l-.8-2.2L8 11l2.2-.8L11 8zm6 7l.8 2.2L20 18l-2.2.8L17 21l-.8-2.2L14 18l2.2-.8L17 15z',
            'scan' => 'M4 7V4h3m10 0h3v3M4 17v3h3m10 0h3v-3m-3-7a4 4 0 00-4-4 4 4 0 00-4 4 4 4 0 00-4 4 4 4 0 004 4 4 4 0 004-4 4 4 0 004-4z',
            'bolt' => 'M13 10V3L4 14h7v7l9-11h-7z',
            'briefcase' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            'badge' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 112 0v1m-2 1a2 2 0 002 2h2a2 2 0 002-2m-6 1v5m0 0l-2-2m2 2l2-2',
        ];
    ?>

    <div x-data="{ loading: true }" x-init="setTimeout(() => loading = false, 300)">
        
        <div x-show="loading" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 mb-6">
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

        
        <div x-show="!loading" x-cloak>
    <?php if(!empty($quickActions)): ?>
        <div class="mb-6 flex flex-wrap gap-2">
            <?php $__currentLoopData = $quickActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(route($action['route'])); ?>" class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium transition
                    <?php echo e($action['color'] === 'blue' ? 'bg-blue-50 text-blue-700 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20' : ''); ?>

                    <?php echo e($action['color'] === 'violet' ? 'bg-violet-50 text-violet-700 hover:bg-violet-100 dark:bg-violet-500/10 dark:text-violet-400 dark:hover:bg-violet-500/20' : ''); ?>

                    <?php echo e($action['color'] === 'amber' ? 'bg-amber-50 text-amber-700 hover:bg-amber-100 dark:bg-amber-500/10 dark:text-amber-400 dark:hover:bg-amber-500/20' : ''); ?>

                    <?php echo e($action['color'] === 'green' ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-400 dark:hover:bg-emerald-500/20' : ''); ?>

                    <?php echo e($action['color'] === 'gray' ? 'bg-gray-50 text-gray-700 hover:bg-gray-100 dark:bg-gray-500/10 dark:text-gray-400 dark:hover:bg-gray-500/20' : ''); ?>

                    <?php echo e($action['color'] === 'purple' ? 'bg-purple-50 text-purple-700 hover:bg-purple-100 dark:bg-purple-500/10 dark:text-purple-400 dark:hover:bg-purple-500/20' : ''); ?>">
                    <?php echo e($action['label']); ?>

                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <?php if($user->hasAnyPermission(['customers.view', 'leads.view']) || $isExecutive): ?>
                    <?php if (isset($component)) { $__componentOriginal457ade557f73eaa008f851091260abe1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal457ade557f73eaa008f851091260abe1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Customers','value' => $stats['customers'],'icon' => 'users','color' => 'blue','url' => route('customers.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Customers','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['customers']),'icon' => 'users','color' => 'blue','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('customers.index'))]); ?>
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
                <?php endif; ?>
                <?php if($user->hasPermissionTo('leads.view') || $isExecutive): ?>
                    <?php if (isset($component)) { $__componentOriginal457ade557f73eaa008f851091260abe1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal457ade557f73eaa008f851091260abe1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Leads','value' => $stats['leads'],'icon' => 'target','color' => 'violet','url' => route('leads.index'),'sub' => 'New: '.$stats['leads_new']]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Leads','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['leads']),'icon' => 'target','color' => 'violet','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('leads.index')),'sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('New: '.$stats['leads_new'])]); ?>
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
                <?php endif; ?>
                <?php if($user->hasPermissionTo('leads.view') || $isExecutive): ?>
                    <?php if (isset($component)) { $__componentOriginal457ade557f73eaa008f851091260abe1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal457ade557f73eaa008f851091260abe1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Open Opportunities','value' => $stats['open_opportunities'],'icon' => 'trending','color' => 'amber','url' => route('opportunities.index')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Open Opportunities','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($stats['open_opportunities']),'icon' => 'trending','color' => 'amber','url' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('opportunities.index'))]); ?>
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
                <?php endif; ?>
                <?php if($user->hasPermissionTo('finance.view') || $isExecutive): ?>
                    <?php if (isset($component)) { $__componentOriginal457ade557f73eaa008f851091260abe1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal457ade557f73eaa008f851091260abe1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dashboard.stat-card','data' => ['label' => 'Pipeline Value','value' => '₹'.number_format($stats['pipeline_value']),'icon' => 'wallet','color' => 'green','sub' => 'Won: ₹'.number_format($stats['won_value'])]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dashboard.stat-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pipeline Value','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('₹'.number_format($stats['pipeline_value'])),'icon' => 'wallet','color' => 'green','sub' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Won: ₹'.number_format($stats['won_value']))]); ?>
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
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if($isExecutive && $aiQuota): ?>
        <div class="mt-6">
            <div class="card">
                <div class="flex items-center justify-between p-4">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-500/15">
                            <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['spark']); ?>"/></svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-ink-800 dark:text-ink-100">AI Usage</p>
                            <p class="text-xs text-ink-400 dark:text-ink-500">Your quota: <?php echo e($aiQuota['daily_used']); ?>/<?php echo e($aiQuota['daily_limit']); ?> daily, <?php echo e($aiQuota['weekly_used']); ?>/<?php echo e($aiQuota['weekly_limit']); ?> weekly</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <?php if($aiQuota['alerts']): ?>
                            <?php $__currentLoopData = $aiQuota['alerts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                    <?php echo e($alert >= 90 ? 'bg-red-100 text-red-700 dark:bg-red-500/15 dark:text-red-400' : ($alert >= 75 ? 'bg-amber-100 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-gray-100 text-gray-600 dark:bg-ink-700 dark:text-ink-300')); ?>">
                                    <?php echo e($alert); ?>% used
                                </span>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <span class="inline-flex items-center rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400">Healthy</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="mt-6">
        <div class="card" x-data="aiInsights()">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">AI Business Insights</h2>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-ink-400 dark:text-ink-500" x-show="status" x-text="status"></span>
                    <button type="button" @click="generate()" :disabled="busy" x-show="<?php echo e($aiEnabled ? 'true' : 'false'); ?>"
                        class="btn btn-sm" :class="busy ? 'opacity-50' : ''">
                        <span x-show="!busy">Generate AI insights</span>
                        <span x-show="busy">Generating…</span>
                    </button>
                </div>
            </div>

            <div class="p-5">
                <div id="ai-rule-insights" class="space-y-2">
                    <?php $__empty_1 = true; $__currentLoopData = $ruleInsights; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insight): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php if($insight['url']): ?>
                            <a href="<?php echo e($insight['url']); ?>" class="flex items-start gap-3 rounded-lg border p-3 transition <?php echo e($insight['tone'] === 'critical' ? 'border-red-200 bg-red-50/60 hover:bg-red-50 dark:border-red-800/50 dark:bg-red-500/10 dark:hover:bg-red-500/15' : ($insight['tone'] === 'high' ? 'border-amber-200 bg-amber-50/60 hover:bg-amber-50 dark:border-amber-800/50 dark:bg-amber-500/10 dark:hover:bg-amber-500/15' : 'border-ink-100 hover:bg-ink-50 dark:border-ink-800 dark:hover:bg-ink-800/50')); ?>">
                        <?php else: ?>
                            <div class="flex items-start gap-3 rounded-lg border border-ink-100 p-3 dark:border-ink-800">
                        <?php endif; ?>
                            <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full <?php echo e($insight['tone'] === 'critical' ? 'bg-red-100 text-red-600 dark:bg-red-500/15 dark:text-red-400' : ($insight['tone'] === 'high' ? 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-400' : 'bg-navy-100 text-navy-600 dark:bg-navy-500/15 dark:text-navy-400')); ?> text-xs font-bold">!</span>
                            <p class="text-sm text-ink-700 dark:text-ink-300"><?php echo e($insight['text']); ?></p>
                        <?php if($insight['url']): ?>
                            </a>
                        <?php else: ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-ink-400 dark:text-ink-500">No urgent signals right now.</p>
                    <?php endif; ?>
                </div>

                <div id="ai-generated" class="mt-4 space-y-2" x-show="content" x-cloak>
                    <div class="rounded-lg border border-navy-100 bg-navy-50/50 p-4 dark:border-navy-800/50 dark:bg-navy-500/10">
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-navy-600 dark:text-navy-400">AI summary
                            <span class="ml-1 font-normal normal-case text-ink-400 dark:text-ink-500" x-text="meta"></span>
                        </p>
                        <p class="whitespace-pre-wrap text-sm text-ink-700 dark:text-ink-300" x-text="content"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if($user->hasPermissionTo('leads.view') || $isExecutive): ?>
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="card xl:col-span-2">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Pipeline by Stage</h2>
                <span class="text-xs text-ink-400 dark:text-ink-500">Opportunities</span>
            </div>
            <div class="p-5">
                <div class="flex h-3 w-full overflow-hidden rounded-full bg-ink-100 dark:bg-ink-800" x-data="{}">
                    <?php
                        $total = max(1, array_sum($opportunitiesByStage));
                        $colors = ['qualification' => 'bg-ink-400', 'needs_analysis' => 'bg-navy-400', 'proposal' => 'bg-navy-600', 'negotiation' => 'bg-amber-400', 'won' => 'bg-emerald-500', 'lost' => 'bg-red-400'];
                        $labels = \App\Models\Opportunity::STAGES;
                    ?>
                    <?php $__currentLoopData = $opportunitiesByStage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="<?php echo e($colors[$stage] ?? 'bg-ink-300'); ?> h-3" style="width: <?php echo e($count / $total * 100); ?>%" title="<?php echo e(($labels[$stage] ?? $stage)); ?>: <?php echo e($count); ?>"></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3">
                    <?php $__currentLoopData = $opportunitiesByStage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stage => $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-2 text-xs text-ink-600 dark:text-ink-400">
                            <span class="h-2.5 w-2.5 rounded-full <?php echo e($colors[$stage] ?? 'bg-ink-300'); ?>"></span>
                            <?php echo e($labels[$stage] ?? $stage); ?> — <?php echo e($count); ?>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Follow-ups due soon</h2>
                <a href="<?php echo e(route('leads.index')); ?>" class="text-xs font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">View all</a>
            </div>
            <div class="divide-y divide-ink-100 dark:divide-ink-800">
                <?php $__empty_1 = true; $__currentLoopData = $followUps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lead): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e(route('leads.show', $lead)); ?>" class="flex items-start gap-3 p-4 hover:bg-ink-50 dark:hover:bg-ink-800/50">
                        <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-50 text-xs font-bold text-amber-600 dark:bg-amber-500/15 dark:text-amber-400"><?php echo e($lead->contact_name[0] ?? '?'); ?></span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-ink-800 dark:text-ink-100"><?php echo e($lead->contact_name); ?></p>
                            <p class="truncate text-xs text-ink-500 dark:text-ink-400"><?php echo e($lead->company_name); ?></p>
                            <p class="mt-1 text-xs font-semibold <?php echo e($lead->next_follow_up->isPast() ? 'text-red-600 dark:text-red-400' : 'text-ink-400 dark:text-ink-500'); ?>">Due <?php echo e($lead->next_follow_up->diffForHumans()); ?></p>
                        </div>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="p-4 text-sm text-ink-400 dark:text-ink-500">No follow-ups scheduled.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if($user->hasAnyPermission(['supplier_offers.view', 'suppliers.view']) || $isExecutive): ?>
    <div class="mt-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Recent Supplier Offers</h2>
                <a href="<?php echo e(route('supplier-offers.index')); ?>" class="text-xs font-medium text-navy-600 hover:text-navy-500 dark:text-navy-400 dark:hover:text-navy-300">All offers</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Material</th>
                            <th>Qty (MT)</th>
                            <th>Price/MT</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $recentOffers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $offer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td class="font-medium text-ink-800 dark:text-ink-100"><?php echo e($offer->supplier?->name ?? 'Unknown'); ?></td>
                                <td><?php echo e($offer->material_description); ?></td>
                                <td><?php echo e(number_format((float) $offer->quantity_mt, 2)); ?></td>
                                <td><?php echo e($offer->currency_code); ?> <?php echo e(number_format((float) $offer->price_per_mt, 2)); ?></td>
                                <td>
                                    <span class="<?php echo e($offer->quality_status === 'GREEN' ? 'badge-green' : ($offer->quality_status === 'RED' ? 'badge-red' : 'badge-yellow')); ?>"><?php echo e($offer->quality_status ?? '—'); ?></span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="py-6 text-center text-ink-400 dark:text-ink-500">No offers yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Procurement Risk Overview</h2>
                <span class="text-xs text-ink-400 dark:text-ink-500"><?php echo e($offers['total']); ?> offers analysed</span>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-5">
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-center dark:border-emerald-800/50 dark:bg-emerald-500/10">
                    <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400"><?php echo e($offers['green']); ?></p>
                    <p class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">GREEN</p>
                </div>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-center dark:border-amber-800/50 dark:bg-amber-500/10">
                    <p class="text-2xl font-bold text-amber-600 dark:text-amber-400"><?php echo e($offers['yellow']); ?></p>
                    <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">YELLOW</p>
                </div>
                <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-center dark:border-red-800/50 dark:bg-red-500/10">
                    <p class="text-2xl font-bold text-red-600 dark:text-red-400"><?php echo e($offers['red']); ?></p>
                    <p class="text-xs font-semibold text-red-700 dark:text-red-400">RED</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-6">
        <div class="card">
            <div class="card-header">
                <h2 class="text-sm font-bold text-ink-800 dark:text-ink-100">Module Rollout</h2>
                <span class="text-xs text-ink-400 dark:text-ink-500">Incremental delivery plan</span>
            </div>
            <div class="grid grid-cols-2 gap-3 p-5 sm:grid-cols-3 lg:grid-cols-4">
                <?php $__currentLoopData = $modules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if($module['route']): ?>
                        <a href="<?php echo e(route($module['route'])); ?>" class="flex items-center gap-3 rounded-lg border p-3 transition <?php echo e($module['ready'] ? 'border-emerald-200 bg-emerald-50/50 hover:bg-emerald-50 dark:border-emerald-800/50 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/15' : 'border-ink-200 bg-white hover:bg-ink-50 dark:border-ink-800 dark:bg-ink-800/50 dark:hover:bg-ink-800'); ?>">
                    <?php else: ?>
                        <div class="flex items-center gap-3 rounded-lg border p-3 <?php echo e($module['ready'] ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-800/50 dark:bg-emerald-500/10' : 'border-ink-200 bg-white dark:border-ink-800 dark:bg-ink-800/50'); ?>">
                    <?php endif; ?>
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg <?php echo e($module['ready'] ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-400' : 'bg-ink-100 text-ink-400 dark:bg-ink-800 dark:text-ink-500'); ?>">
                            <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons[$module['icon']] ?? ''); ?>"/></svg>
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold text-ink-800 dark:text-ink-100"><?php echo e($module['name']); ?></p>
                            <p class="text-xs <?php echo e($module['ready'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-ink-400 dark:text-ink-500'); ?>"><?php echo e($module['ready'] ? 'Live' : 'Planned'); ?></p>
                        </div>
                    <?php if($module['route']): ?>
                        </a>
                    <?php else: ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        function aiInsights() {
            return {
                busy: false,
                status: '',
                content: '',
                meta: '',
                init() {
                    const existing = <?php echo json_encode($aiInsights ? ['content' => $aiInsights['content'], 'generated_at' => $aiInsights['generated_at'], 'provider' => $aiInsights['provider']] : null) ?>;
                    if (existing) {
                        this.content = existing.content;
                        this.meta = 'Generated ' + new Date(existing.generated_at).toLocaleString() + ' · ' + existing.provider;
                    }
                },
                async generate() {
                    if (this.busy) return;
                    this.busy = true;
                    this.status = 'Analysing your data…';
                    try {
                        const res = await fetch(<?php echo json_encode(route('ai.insights.generate'), 15, 512) ?>, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        });
                        const data = await res.json().catch(() => ({}));
                        if (res.ok) {
                            this.content = data.content;
                            this.meta = 'Generated ' + new Date(data.generated_at).toLocaleString() + ' · ' + data.provider;
                            this.status = 'Updated';
                        } else {
                            this.status = '';
                            alert(data.error || 'AI is not configured yet. Set RAPIDAPI_KEY to enable AI insights.');
                        }
                    } catch (err) {
                        this.status = '';
                        alert('Could not reach the server. Please try again.');
                    } finally {
                        this.busy = false;
                    }
                }
            };
        }
    </script>
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
<?php /**PATH C:\MY_ERP\resources\views/dashboard/index.blade.php ENDPATH**/ ?>