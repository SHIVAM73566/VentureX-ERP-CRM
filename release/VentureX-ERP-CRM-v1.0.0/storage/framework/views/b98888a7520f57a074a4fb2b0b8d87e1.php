<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice <?php echo e($invoice->invoice_number); ?></title>
    <link rel="icon" type="image/svg+xml" href="<?php echo e(asset('favicon.svg')); ?>">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #1a365d; padding-bottom: 20px; }
        .company-name { font-size: 22px; font-weight: bold; color: #1a365d; }
        .doc-title { font-size: 28px; font-weight: bold; color: #1a365d; text-align: right; }
        .doc-number { font-size: 14px; color: #666; text-align: right; }
        .status { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; margin-top: 5px; }
        .status-draft { background: #e5e7eb; color: #374151; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-partial { background: #fef3c7; color: #92400e; }
        .status-paid { background: #d1fae5; color: #065f46; }
        .status-overdue { background: #fee2e2; color: #991b1b; }
        .info-row { display: flex; margin-bottom: 20px; }
        .info-block { width: 50%; }
        .info-label { font-size: 10px; text-transform: uppercase; color: #888; font-weight: bold; letter-spacing: 0.5px; }
        .info-value { font-size: 12px; margin-top: 3px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th { background: #1a365d; color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
        td { padding: 10px 12px; border-bottom: 1px solid #e5e7eb; }
        .text-right { text-align: right; }
        .totals { width: 300px; margin-left: auto; margin-top: 20px; }
        .totals td { padding: 6px 12px; }
        .totals tr.total-row td { border-top: 2px solid #1a365d; font-weight: bold; font-size: 14px; }
        .totals tr.paid-row td { color: #065f46; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #888; }
        .notes { margin-top: 20px; padding: 12px; background: #f9fafb; border-radius: 4px; }
        .notes-label { font-weight: bold; font-size: 11px; text-transform: uppercase; color: #555; }
        .payment-info { margin-top: 15px; padding: 12px; background: #eff6ff; border-radius: 4px; border-left: 3px solid #1a365d; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="company-name">VentureX ERP & CRM</div>
            <div style="font-size: 11px; color: #666; margin-top: 5px;">Universal CRM + ERP Business Operating System</div>
        </div>
        <div>
            <div class="doc-title">INVOICE</div>
            <div class="doc-number"><?php echo e($invoice->invoice_number); ?></div>
            <div><span class="status status-<?php echo e($invoice->status); ?>"><?php echo e(ucfirst(str_replace('_', ' ', $invoice->status))); ?></span></div>
        </div>
    </div>

    <div class="info-row">
        <div class="info-block">
            <div class="info-label">Bill To</div>
            <div class="info-value">
                <strong><?php echo e($invoice->customer->name ?? 'N/A'); ?></strong><br>
                <?php echo e($invoice->customer->email ?? ''); ?><br>
                <?php echo e($invoice->customer->phone ?? ''); ?><br>
                <?php echo e($invoice->customer->address ?? ''); ?>

            </div>
        </div>
        <div class="info-block" style="text-align: right;">
            <div class="info-label">Details</div>
            <div class="info-value">
                <strong>Issue Date:</strong> <?php echo e($invoice->issue_date?->format('d M Y') ?? 'N/A'); ?><br>
                <?php if($invoice->due_date): ?>
                    <strong>Due Date:</strong> <?php echo e($invoice->due_date->format('d M Y')); ?><br>
                <?php endif; ?>
                <?php if($invoice->salesOrder): ?>
                    <strong>SO Reference:</strong> <?php echo e($invoice->salesOrder->order_number); ?><br>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Discount</th>
                <th class="text-right">Tax</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            <?php $__currentLoopData = $invoice->items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <tr>
                <td><?php echo e($loop->iteration); ?></td>
                <td><?php echo e($item->description); ?><?php if($item->product): ?><br><small style="color:#888">SKU: <?php echo e($item->product->sku ?? ''); ?></small><?php endif; ?></td>
                <td class="text-right"><?php echo e(number_format($item->quantity, 2)); ?></td>
                <td class="text-right"><?php echo e(number_format($item->unit_price, 2)); ?></td>
                <td class="text-right"><?php echo e(number_format($item->discount, 2)); ?></td>
                <td class="text-right"><?php echo e(number_format($item->tax_rate, 1)); ?>%</td>
                <td class="text-right"><?php echo e(number_format($item->line_total, 2)); ?></td>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td class="text-right"><?php echo e(number_format($invoice->subtotal, 2)); ?></td>
        </tr>
        <tr>
            <td>Discount</td>
            <td class="text-right">-<?php echo e(number_format($invoice->discount, 2)); ?></td>
        </tr>
        <tr>
            <td>Tax</td>
            <td class="text-right"><?php echo e(number_format($invoice->tax, 2)); ?></td>
        </tr>
        <tr class="total-row">
            <td>Total</td>
            <td class="text-right"><?php echo e(number_format($invoice->total, 2)); ?></td>
        </tr>
        <?php if($invoice->paid_amount > 0): ?>
        <tr class="paid-row">
            <td>Paid</td>
            <td class="text-right">-<?php echo e(number_format($invoice->paid_amount, 2)); ?></td>
        </tr>
        <tr class="total-row">
            <td>Balance Due</td>
            <td class="text-right"><?php echo e(number_format($invoice->total - $invoice->paid_amount, 2)); ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <?php if($invoice->payments->count() > 0): ?>
    <div class="payment-info">
        <div class="notes-label">Payment History</div>
        <?php $__currentLoopData = $invoice->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div style="margin-top: 5px;">
                <?php echo e($payment->payment_date?->format('d M Y')); ?> &mdash; <?php echo e(strtoupper($payment->method)); ?> &mdash;
                <?php echo e(number_format($payment->amount, 2)); ?> &mdash;
                <span style="text-transform: uppercase; font-weight: bold;"><?php echo e($payment->status); ?></span>
                <?php if($payment->reference): ?> (Ref: <?php echo e($payment->reference); ?>) <?php endif; ?>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <?php if($invoice->notes): ?>
    <div class="notes">
        <div class="notes-label">Notes</div>
        <div><?php echo e($invoice->notes); ?></div>
    </div>
    <?php endif; ?>

    <div class="footer">
        Generated by VentureX ERP & CRM &mdash; <?php echo e(now()->format('d M Y H:i')); ?>

    </div>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views/sales/invoices/pdf.blade.php ENDPATH**/ ?>