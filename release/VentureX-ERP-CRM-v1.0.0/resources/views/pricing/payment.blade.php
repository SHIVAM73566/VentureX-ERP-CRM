<x-layouts.app title="Pay with Payoneer">
    <div class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200 dark:border-ink-800 p-8 shadow-lg">
            <div class="text-center mb-8">
                <div class="w-16 h-16 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
                <h1 class="text-2xl font-bold text-ink-900 dark:text-ink-50 mb-2">Pay with Payoneer</h1>
                <p class="text-ink-500 dark:text-ink-400">Send payment to complete your license purchase</p>
            </div>

            {{-- Payment Details Card --}}
            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 mb-6">
                <h2 class="text-sm font-semibold text-blue-800 dark:text-blue-300 uppercase tracking-wide mb-4">Payment Details</h2>

                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-ink-600 dark:text-ink-400 text-sm">License</span>
                        <span class="font-semibold text-ink-900 dark:text-ink-50">{{ $session['license_name'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-ink-600 dark:text-ink-400 text-sm">Amount</span>
                        <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $session['currency'] }} {{ number_format($session['amount'], 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-ink-600 dark:text-ink-400 text-sm">Reference Code</span>
                        <span class="font-mono font-semibold text-ink-900 dark:text-ink-50 bg-white dark:bg-ink-800 px-3 py-1 rounded-lg border border-ink-200 dark:border-ink-700">{{ $session['reference'] }}</span>
                    </div>
                </div>
            </div>

            {{-- Payoneer Email --}}
            <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-6 mb-6">
                <h2 class="text-sm font-semibold text-emerald-800 dark:text-emerald-300 uppercase tracking-wide mb-3">Send Payment To</h2>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/40 rounded-full flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs text-emerald-600 dark:text-emerald-400">Payoneer Email</p>
                        <p class="font-mono font-bold text-emerald-800 dark:text-emerald-200 text-lg">{{ $session['payoneer_email'] }}</p>
                    </div>
                </div>
            </div>

            {{-- Instructions --}}
            <div class="mb-6">
                <h2 class="text-sm font-semibold text-ink-700 dark:text-ink-300 uppercase tracking-wide mb-3">How to Pay</h2>
                <ol class="space-y-3">
                    <li class="flex gap-3">
                        <span class="w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-full flex items-center justify-center shrink-0 mt-0.5">1</span>
                        <span class="text-ink-600 dark:text-ink-300 text-sm">Log in to your <strong>Payoneer</strong> account at <a href="https://www.payoneer.com" target="_blank" class="text-blue-600 hover:underline">payoneer.com</a></span>
                    </li>
                    <li class="flex gap-3">
                        <span class="w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-full flex items-center justify-center shrink-0 mt-0.5">2</span>
                        <span class="text-ink-600 dark:text-ink-300 text-sm">Go to <strong>Pay</strong> → <strong>Pay to Email</strong></span>
                    </li>
                    <li class="flex gap-3">
                        <span class="w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-full flex items-center justify-center shrink-0 mt-0.5">3</span>
                        <span class="text-ink-600 dark:text-ink-300 text-sm">Enter the Payoneer email address shown above</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-full flex items-center justify-center shrink-0 mt-0.5">4</span>
                        <span class="text-ink-600 dark:text-ink-300 text-sm">Enter the exact amount: <strong class="text-ink-900 dark:text-ink-50">{{ $session['currency'] }} {{ number_format($session['amount'], 2) }}</strong></span>
                    </li>
                    <li class="flex gap-3">
                        <span class="w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-full flex items-center justify-center shrink-0 mt-0.5">5</span>
                        <span class="text-ink-600 dark:text-ink-300 text-sm">Add reference: <code class="bg-ink-100 dark:bg-ink-800 px-2 py-0.5 rounded text-xs font-mono">{{ $session['reference'] }}</code></span>
                    </li>
                    <li class="flex gap-3">
                        <span class="w-6 h-6 bg-blue-600 text-white text-xs font-bold rounded-full flex items-center justify-center shrink-0 mt-0.5">6</span>
                        <span class="text-ink-600 dark:text-ink-300 text-sm">Complete the payment. Your license will be activated within <strong>24 hours</strong>.</span>
                    </li>
                </ol>
            </div>

            {{-- Important Notes --}}
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl p-4 mb-6">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    <div class="text-sm">
                        <p class="font-semibold text-amber-800 dark:text-amber-300 mb-1">Important</p>
                        <ul class="text-amber-700 dark:text-amber-400 space-y-1">
                            <li>• Include the reference code in your payment note/description</li>
                            <li>• Send the exact amount to avoid delays</li>
                            <li>• You will receive a confirmation email once payment is verified</li>
                            <li>• License key will be delivered via email within 24 hours</li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <a href="{{ route('pricing') }}" class="flex-1 rounded-lg border border-ink-300 dark:border-ink-600 bg-white dark:bg-ink-800 px-4 py-3 text-sm font-semibold text-ink-700 dark:text-ink-200 hover:bg-ink-50 dark:hover:bg-ink-700 transition text-center">Back to Pricing</a>
                <a href="mailto:support@venturex-erp.com?subject=Payment%20Help%20-%20{{ $session['reference'] }}" class="flex-1 rounded-lg bg-blue-600 hover:bg-blue-700 px-4 py-3 text-sm font-semibold text-white transition text-center">Need Help?</a>
            </div>
        </div>
    </div>
</x-layouts.app>
