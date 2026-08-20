<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Pricing — <?php echo e(config('app.name')); ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('favicon.svg')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        .plan-card { transition: transform 0.2s, box-shadow 0.2s; }
        .plan-card:hover { transform: translateY(-4px); box-shadow: 0 12px 40px rgba(0,0,0,0.3); }
        .plan-featured { border: 2px solid #3b82f6; }
        .paypal-btn-container { min-height: 50px; }
    </style>
</head>
<body class="bg-gray-950 text-white min-h-screen">
    <div class="max-w-6xl mx-auto px-4 py-12">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold mb-4">Choose Your Plan</h1>
            <p class="text-gray-400 text-lg">One-time payment. Lifetime access. No subscriptions.</p>
            <?php if(!$isConfigured): ?>
                <div class="mt-4 bg-yellow-900/50 border border-yellow-700 rounded-lg p-4 text-yellow-300 text-sm">
                    PayPal is not configured. Set PAYPAL_CLIENT_ID and PAYPAL_CLIENT_SECRET in your .env file.
                </div>
            <?php endif; ?>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php $__currentLoopData = ['starter', 'professional', 'enterprise']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $planKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php $plan = $plans[$planKey]; ?>
                <div class="plan-card bg-gray-900 rounded-2xl p-8 flex flex-col <?php echo e($planKey === 'professional' ? 'plan-featured relative' : ''); ?>">
                    <?php if($planKey === 'professional'): ?>
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-blue-600 text-white text-xs font-bold px-4 py-1 rounded-full">MOST POPULAR</div>
                    <?php endif; ?>

                    <h3 class="text-xl font-bold mb-2"><?php echo e($plan['name']); ?></h3>
                    <div class="text-4xl font-extrabold mb-4">
                        $<?php echo e(number_format($plan['price_cents'] / 100, 0)); ?>

                        <span class="text-base font-normal text-gray-400">one-time</span>
                    </div>
                    <p class="text-gray-400 text-sm mb-6 flex-grow"><?php echo e($plan['description']); ?></p>

                    <ul class="space-y-3 mb-8 text-sm text-gray-300">
                        <?php if($planKey === 'starter'): ?>
                            <li>✓ Single site license</li>
                            <li>✓ 6 months support</li>
                            <li>✓ All core modules</li>
                            <li>✓ AI assistant (basic)</li>
                        <?php elseif($planKey === 'professional'): ?>
                            <li>✓ Single site license</li>
                            <li>✓ 12 months support</li>
                            <li>✓ All modules + AI</li>
                            <li>✓ Priority email support</li>
                            <li>✓ Free updates</li>
                        <?php else: ?>
                            <li>✓ Unlimited sites</li>
                            <li>✓ Lifetime support</li>
                            <li>✓ Full source code</li>
                            <li>✓ Custom branding</li>
                            <li>✓ Direct developer access</li>
                        <?php endif; ?>
                    </ul>

                    <div class="paypal-btn-container" id="paypal-button-<?php echo e($planKey); ?>"></div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        <div class="text-center mt-12">
            <a href="<?php echo e(route('payment.send')); ?>" class="text-blue-400 hover:text-blue-300 underline text-sm">
                Want to request a custom feature or send a payment? Click here →
            </a>
        </div>
    </div>

    <?php if($isConfigured): ?>
    <script src="https://www.paypal.com/sdk/js?client-id=<?php echo e($clientId); ?>&currency=USD"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const plans = <?php echo json_encode($plans, 15, 512) ?>;

            Object.keys(plans).forEach(function(planKey) {
                if (planKey === 'custom_donation') return;
                const plan = plans[planKey];
                const container = document.getElementById('paypal-button-' + planKey);
                if (!container) return;

                paypal.Buttons({
                    style: { layout: 'vertical', color: 'blue', shape: 'rect', label: 'pay' },
                    createOrder: function(data, actions) {
                        return fetch('<?php echo e(route("payment.create-order")); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ plan: planKey })
                        }).then(function(res) { return res.json(); })
                          .then(function(order) { return order.id; });
                    },
                    onApprove: function(data) {
                        return fetch('<?php echo e(route("payment.capture")); ?>', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ order_id: data.orderID })
                        }).then(function(res) { return res.json(); })
                          .then(function(result) {
                              if (result.success) {
                                  window.location.href = '<?php echo e(route("payment.success")); ?>';
                              } else {
                                  alert('Payment failed. Please try again.');
                              }
                          });
                    },
                    onError: function(err) {
                        console.error('PayPal error:', err);
                        alert('Payment failed. Please try again.');
                    }
                }).render('#paypal-button-' + planKey);
            });
        });
    </script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views/paypal/index.blade.php ENDPATH**/ ?>