<?php if (isset($component)) { $__componentOriginal5863877a5171c196453bfa0bd807e410 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5863877a5171c196453bfa0bd807e410 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.layouts.app','data' => ['title' => 'New Journal Entry','breadcrumbs' => [['label' => 'Finance', 'url' => route('finance.journals.index')], ['label' => 'Journal Entries', 'url' => route('finance.journals.index')], ['label' => 'New']]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['title' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('New Journal Entry'),'breadcrumbs' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([['label' => 'Finance', 'url' => route('finance.journals.index')], ['label' => 'Journal Entries', 'url' => route('finance.journals.index')], ['label' => 'New']])]); ?>

    <div class="mx-auto max-w-4xl">
        <form method="POST" action="<?php echo e(route('finance.journals.store')); ?>" class="space-y-6" x-data="journalForm()">
            <?php echo csrf_field(); ?>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Entry Details</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <?php if (isset($component)) { $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input','data' => ['label' => 'Date *','name' => 'date','type' => 'date','value' => ''.e(now()->format('Y-m-d')).'','required' => true]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Date *','name' => 'date','type' => 'date','value' => ''.e(now()->format('Y-m-d')).'','required' => true]); ?>
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
                    <?php if (isset($component)) { $__componentOriginal5c2a97ab476b69c1189ee85d1a95204b = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal5c2a97ab476b69c1189ee85d1a95204b = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.form.input','data' => ['label' => 'Description','name' => 'description','placeholder' => 'e.g. Freight payment to ABC Shipping']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('form.input'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Description','name' => 'description','placeholder' => 'e.g. Freight payment to ABC Shipping']); ?>
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
                </div>
            </div>

            <div class="card space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-ink-900">Lines</h2>
                    <button type="button" @click="addLine()" class="btn-secondary">+ Add Line</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="table">
                        <thead><tr><th>Account *</th><th>Description</th><th class="w-32">Debit</th><th class="w-32">Credit</th><th class="w-10"></th></tr></thead>
                        <tbody>
                            <template x-for="(line, index) in lines" :key="index">
                                <tr>
                                    <td>
                                        <select :name="'lines['+index+'][account_id]'" x-model="line.account_id" class="input" required>
                                            <option value="">Account…</option>
                                            <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $account): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($account->id); ?>"><?php echo e($account->code); ?> — <?php echo e($account->name); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </td>
                                    <td><input type="text" :name="'lines['+index+'][description]'" x-model="line.description" class="input" /></td>
                                    <td><input type="number" step="0.01" min="0" :name="'lines['+index+'][debit]'" x-model.number="line.debit" class="input" @input="clearCredit(index)" /></td>
                                    <td><input type="number" step="0.01" min="0" :name="'lines['+index+'][credit]'" x-model.number="line.credit" class="input" @input="clearDebit(index)" /></td>
                                    <td><button type="button" @click="lines.splice(index, 1)" class="text-red-500 hover:text-red-400">✕</button></td>
                                </tr>
                            </template>
                            <tr x-show="lines.length === 0"><td colspan="5" class="py-6 text-center text-ink-400">No lines yet.</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col items-end gap-1 text-sm">
                    <div class="flex justify-between w-full max-w-xs"><span class="text-ink-400">Total Debits</span><span class="font-semibold text-emerald-600" x-text="totals().debit.toFixed(2)"></span></div>
                    <div class="flex justify-between w-full max-w-xs"><span class="text-ink-400">Total Credits</span><span class="font-semibold text-red-600" x-text="totals().credit.toFixed(2)"></span></div>
                    <div class="flex justify-between w-full max-w-xs border-t border-ink-200 pt-1">
                        <span class="font-semibold text-ink-700">Balance</span>
                        <span class="font-bold" :class="totals().balance === 0 ? 'text-emerald-600' : 'text-red-600'" x-text="totals().balance.toFixed(2)"></span>
                    </div>
                    <p class="text-xs text-ink-400" x-show="totals().balance !== 0">Debits must equal credits to post.</p>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="<?php echo e(route('finance.journals.index')); ?>" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">Create Draft Entry</button>
            </div>
        </form>
    </div>

    <script>
        function journalForm() {
            return {
                lines: [{ account_id: '', description: '', debit: 0, credit: 0 }],
                addLine() { this.lines.push({ account_id: '', description: '', debit: 0, credit: 0 }); },
                clearCredit(index) { if (this.lines[index].debit > 0) this.lines[index].credit = 0; },
                clearDebit(index) { if (this.lines[index].credit > 0) this.lines[index].debit = 0; },
                totals() {
                    let debit = 0, credit = 0;
                    this.lines.forEach(l => { debit += l.debit || 0; credit += l.credit || 0; });
                    return { debit, credit, balance: +(debit - credit).toFixed(2) };
                },
            };
        }
    </script>
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
<?php /**PATH C:\MY_ERP\resources\views/finance/journals/form.blade.php ENDPATH**/ ?>