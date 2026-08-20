<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('favicon.svg')); ?>">
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="bg-gray-950 text-white min-h-screen">
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-2xl font-bold">Payment History</h1>
            <a href="<?php echo e(route('payment.index')); ?>" class="text-blue-400 hover:text-blue-300 text-sm">← Back to Pricing</a>
        </div>

        <?php if($payments->isEmpty()): ?>
            <div class="bg-gray-900 rounded-xl p-12 text-center">
                <p class="text-gray-400 text-lg mb-4">No payments yet.</p>
                <a href="<?php echo e(route('payment.index')); ?>" class="text-blue-400 hover:text-blue-300 underline">View plans →</a>
            </div>
        <?php else: ?>
            <div class="bg-gray-900 rounded-xl overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-gray-800 text-gray-400 text-sm">
                        <tr>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Description</th>
                            <th class="px-6 py-3">Amount</th>
                            <th class="px-6 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        <?php $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-800/50">
                                <td class="px-6 py-4 text-sm text-gray-400"><?php echo e($payment->created_at->format('M d, Y')); ?></td>
                                <td class="px-6 py-4 text-sm"><?php echo e(Str::limit($payment->notes ?? $payment->reference, 50)); ?></td>
                                <td class="px-6 py-4 text-sm font-medium">$<?php echo e(number_format((float) $payment->amount, 2)); ?></td>
                                <td class="px-6 py-4 text-sm">
                                    <?php if($payment->isCompleted()): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-900/50 text-green-300 border border-green-800">Completed</span>
                                    <?php elseif($payment->isPending()): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-900/50 text-yellow-300 border border-yellow-800">Pending</span>
                                    <?php elseif($payment->isRefunded()): ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-900/50 text-blue-300 border border-blue-800">Refunded</span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-900/50 text-red-300 border border-red-800">Failed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <div class="mt-4"><?php echo e($payments->links()); ?></div>
        <?php endif; ?>
    </div>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views/paypal/history.blade.php ENDPATH**/ ?>