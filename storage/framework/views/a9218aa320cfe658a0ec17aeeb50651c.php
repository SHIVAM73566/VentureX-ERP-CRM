<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'Audit Log — #'.$log->id,'breadcrumbs' => [['label' => 'Admin', 'url' => route('admin.audit-logs.index')], ['label' => 'Audit Logs', 'url' => route('admin.audit-logs.index')], ['label' => '#'.$log->id]]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Audit Log — #'.$log->id),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Admin', 'url' => route('admin.audit-logs.index')], ['label' => 'Audit Logs', 'url' => route('admin.audit-logs.index')], ['label' => '#'.$log->id]])]); ?>

    <?php
        $sensitiveKeys = ['password','password_hash','two_factor_secret','two_factor_recovery_codes','remember_token','api_token','license_key','secret','token','credit_card','ssn','aadhaar','pan','email','phone','mobile','tax_id'];

        function redactAuditData(array $data, array $sensitiveKeys): array {
            foreach ($data as $key => $value) {
                if (in_array(strtolower($key), $sensitiveKeys) || str_contains(strtolower($key), 'password') || str_contains(strtolower($key), 'secret') || str_contains(strtolower($key), 'token')) {
                    $data[$key] = '[REDACTED]';
                } elseif (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $data[$key] = '[REDACTED_EMAIL]';
                } elseif (is_string($value) && preg_match('/^\+?[\d\s\-()]{7,15}$/', $value)) {
                    $data[$key] = '[REDACTED_PHONE]';
                } elseif (is_array($value)) {
                    $data[$key] = redactAuditData($value, $sensitiveKeys);
                }
            }
            return $data;
        }

        $newVal = $log->new_value ? redactAuditData($log->new_value, $sensitiveKeys) : null;
        $oldVal = $log->old_value ? redactAuditData($log->old_value, $sensitiveKeys) : null;
    ?>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="card">
            <h2 class="mb-3 text-lg font-bold text-ink-900">Log Summary</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-ink-400">Event</dt><dd><span class="badge-<?php echo e($log->event === 'create' ? 'green' : ($log->event === 'update' ? 'blue' : ($log->event === 'delete' ? 'red' : 'amber'))); ?>"><?php echo e($log->event); ?></span></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Module</dt><dd><?php echo e($log->module); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Record</dt><dd><?php echo e($log->record_type); ?> #<?php echo e($log->record_id ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">User</dt><dd><?php echo e($log->user?->name ?? 'System'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Company</dt><dd><?php echo e($log->company?->name ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">Correlation ID</dt><dd class="font-mono text-xs"><?php echo e($log->correlation_id ?? '—'); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">When</dt><dd><?php echo e($log->created_at?->format('d M Y H:i:s')); ?></dd></div>
                <div class="flex justify-between"><dt class="text-ink-400">IP</dt><dd class="font-mono text-xs"><?php echo e($log->ip ?? '—'); ?></dd></div>
            </dl>
            <div class="mt-4 border-t border-ink-100 pt-3">
                <h3 class="text-sm font-bold uppercase tracking-wide text-ink-400">User Agent</h3>
                <p class="mt-1 break-words text-xs text-ink-500"><?php echo e($log->user_agent ?? '—'); ?></p>
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">New Value</h3>
                <pre class="max-h-96 overflow-auto rounded-lg bg-ink-950 p-4 text-xs text-ink-100"><?php echo e($newVal ? json_encode($newVal, JSON_PRETTY_PRINT) : '—'); ?></pre>
            </div>
            <?php if($oldVal): ?>
                <div class="card">
                    <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Old Value</h3>
                    <pre class="max-h-96 overflow-auto rounded-lg bg-ink-950 p-4 text-xs text-ink-100"><?php echo e(json_encode($oldVal, JSON_PRETTY_PRINT)); ?></pre>
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
<?php /**PATH C:\MY_ERP\resources\views\admin\audit-logs\show.blade.php ENDPATH**/ ?>