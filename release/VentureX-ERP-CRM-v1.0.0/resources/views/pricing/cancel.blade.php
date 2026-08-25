<x-layouts.app title="Payment Cancelled">
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="max-w-md w-full text-center">
            <div class="bg-white dark:bg-ink-900 rounded-2xl border border-ink-200 dark:border-ink-800 p-8 shadow-lg">
                <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                    <svg class="h-10 w-10 text-amber-600 dark:text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>

                <h1 class="text-2xl font-bold text-ink-900 dark:text-ink-50 mb-3">Payment Cancelled</h1>
                <p class="text-ink-500 dark:text-ink-400 mb-8">Your payment was not processed. No charges were made.</p>

                <div class="space-y-3">
                    <a href="{{ route('pricing') }}" class="block w-full rounded-lg bg-blue-600 hover:bg-blue-700 px-4 py-3 text-sm font-semibold text-white transition">
                        Try Again
                    </a>
                    <a href="{{ url('/') }}" class="block w-full rounded-lg border border-ink-300 dark:border-ink-600 bg-white dark:bg-ink-800 px-4 py-3 text-sm font-medium text-ink-700 dark:text-ink-200 hover:bg-ink-50 dark:hover:bg-ink-700 transition">
                        Return to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
