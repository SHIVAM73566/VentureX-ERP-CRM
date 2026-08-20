<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Pricing']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => 'Pricing']); ?>
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-3xl font-bold text-ink-900 dark:text-ink-50 mb-3">Choose Your License</h1>
            <p class="text-ink-500 dark:text-ink-400 text-lg">One-time payment. Lifetime access. No subscriptions.</p>
            <?php if(!$isConfigured): ?>
                <div class="mt-4 bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-yellow-700 text-sm dark:bg-yellow-900/20 dark:border-yellow-800 dark:text-yellow-400">
                    Payoneer is not configured. Set PAYONEER_EMAIL in your .env file to enable payments.
                </div>
            <?php endif; ?>
            <?php if($payoneerEmail): ?>
                <p class="mt-2 text-sm text-ink-400">Pay securely via <span class="font-semibold text-blue-600">Payoneer</span></p>
            <?php endif; ?>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            
            <div class="plan-card bg-white dark:bg-ink-900 rounded-2xl border border-ink-200 dark:border-ink-800 p-8 flex flex-col transition-all hover:shadow-lg hover:-translate-y-1">
                <h3 class="text-xl font-bold text-ink-900 dark:text-ink-50 mb-2">Standard</h3>
                <div class="text-4xl font-extrabold text-ink-900 dark:text-ink-50 mb-1">
                    $29
                    <span class="text-sm font-normal text-ink-400 dark:text-ink-500">one-time</span>
                </div>
                <p class="text-ink-500 dark:text-ink-400 text-sm mb-6">Perfect for individuals and small projects getting started.</p>

                <ul class="space-y-3 mb-8 text-sm text-ink-600 dark:text-ink-300 flex-grow">
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Single site license
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        6 months support
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        All core modules
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        AI assistant (basic)
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Community support
                    </li>
                </ul>

                <button type="button" onclick="openCheckout('standard')" class="w-full rounded-lg border border-ink-300 dark:border-ink-600 bg-white dark:bg-ink-800 px-4 py-3 text-sm font-semibold text-ink-700 dark:text-ink-200 hover:bg-ink-50 dark:hover:bg-ink-700 transition">Buy Now — $29</button>
            </div>

            
            <div class="plan-card relative bg-white dark:bg-ink-900 rounded-2xl border-2 border-blue-500 dark:border-blue-400 p-8 flex flex-col transition-all hover:shadow-lg hover:-translate-y-1">
                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-xs font-bold px-4 py-1 rounded-full">MOST POPULAR</div>
                <h3 class="text-xl font-bold text-ink-900 dark:text-ink-50 mb-2">Professional</h3>
                <div class="text-4xl font-extrabold text-ink-900 dark:text-ink-50 mb-1">
                    $59
                    <span class="text-sm font-normal text-ink-400 dark:text-ink-500">one-time</span>
                </div>
                <p class="text-ink-500 dark:text-ink-400 text-sm mb-6">Best value for growing businesses that need full features.</p>

                <ul class="space-y-3 mb-8 text-sm text-ink-600 dark:text-ink-300 flex-grow">
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Single site license
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        12 months support
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        All modules + AI
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Priority email support
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Free updates
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Advanced analytics
                    </li>
                </ul>

                <button type="button" onclick="openCheckout('professional')" class="w-full rounded-lg bg-blue-600 hover:bg-blue-700 px-4 py-3 text-sm font-semibold text-white transition">Buy Now — $59</button>
            </div>

            
            <div class="plan-card bg-white dark:bg-ink-900 rounded-2xl border border-ink-200 dark:border-ink-800 p-8 flex flex-col transition-all hover:shadow-lg hover:-translate-y-1">
                <h3 class="text-xl font-bold text-ink-900 dark:text-ink-50 mb-2">Enterprise</h3>
                <div class="text-4xl font-extrabold text-ink-900 dark:text-ink-50 mb-1">
                    $129
                    <span class="text-sm font-normal text-ink-400 dark:text-ink-500">one-time</span>
                </div>
                <p class="text-ink-500 dark:text-ink-400 text-sm mb-6">For organizations that need source code access and unlimited usage.</p>

                <ul class="space-y-3 mb-8 text-sm text-ink-600 dark:text-ink-300 flex-grow">
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Unlimited sites
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Lifetime support
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Full source code
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Custom branding
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Direct developer access
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="h-4.5 w-4.5 text-emerald-500 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        SLA guarantee
                    </li>
                </ul>

                <button type="button" onclick="openCheckout('enterprise')" class="w-full rounded-lg border border-ink-300 dark:border-ink-600 bg-white dark:bg-ink-800 px-4 py-3 text-sm font-semibold text-ink-700 dark:text-ink-200 hover:bg-ink-50 dark:hover:bg-ink-700 transition">Buy Now — $129</button>
            </div>
        </div>

        
        <div x-data="checkoutModal" x-show="open" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/50 p-4" @click.self="open = false">
            <div class="w-full max-w-md rounded-2xl bg-white dark:bg-ink-900 border border-ink-200 dark:border-ink-800 p-6 shadow-xl">
                <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50 mb-1">Complete Your Purchase</h2>
                <p class="text-sm text-ink-500 dark:text-ink-400 mb-5">You are purchasing the <span class="font-semibold" x-text="tierLabel"></span>.</p>

                <form method="POST" action="<?php echo e(route('pricing.checkout')); ?>" class="space-y-4">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="license_tier" :value="tier">

                    <div>
                        <label for="checkout-name" class="block text-sm font-medium text-ink-700 dark:text-ink-300 mb-1">Full Name</label>
                        <input type="text" name="name" id="checkout-name" required class="w-full rounded-lg border border-ink-300 dark:border-ink-600 bg-white dark:bg-ink-800 px-3 py-2.5 text-sm text-ink-900 dark:text-ink-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="John Doe">
                    </div>

                    <div>
                        <label for="checkout-email" class="block text-sm font-medium text-ink-700 dark:text-ink-300 mb-1">Email Address</label>
                        <input type="email" name="email" id="checkout-email" required class="w-full rounded-lg border border-ink-300 dark:border-ink-600 bg-white dark:bg-ink-800 px-3 py-2.5 text-sm text-ink-900 dark:text-ink-100 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20" placeholder="john@example.com">
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" @click="open = false" class="rounded-lg px-4 py-2.5 text-sm font-medium text-ink-500 hover:bg-ink-100 dark:hover:bg-ink-800 transition">Cancel</button>
                        <button type="submit" class="rounded-lg bg-blue-600 hover:bg-blue-700 px-6 py-2.5 text-sm font-semibold text-white transition">Pay with Payoneer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('checkoutModal', () => ({
                open: false,
                tier: '',
                tierLabel: '',
                init() {
                    window.openCheckout = (tier) => {
                        const labels = {
                            standard: 'Standard License ($29)',
                            professional: 'Professional License ($59)',
                            enterprise: 'Enterprise License ($129)'
                        };
                        this.tier = tier;
                        this.tierLabel = labels[tier] || tier;
                        this.open = true;
                    };
                }
            }));
        });
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
<?php /**PATH C:\MY_ERP\resources\views/pricing/index.blade.php ENDPATH**/ ?>