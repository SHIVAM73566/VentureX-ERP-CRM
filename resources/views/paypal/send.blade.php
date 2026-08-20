<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Send Payment — {{ config('app.name') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center">
    <div class="max-w-lg w-full mx-4" x-data="sendPayment()">
        <div class="bg-gray-900 rounded-2xl p-8 shadow-2xl">
            <h1 class="text-2xl font-bold mb-2">Send a Payment</h1>
            <p class="text-gray-400 text-sm mb-6">
                Request a new feature, send a donation, or make a custom payment.
                Enter your amount and description below.
            </p>

            @if(!$isConfigured)
                <div class="bg-yellow-900/50 border border-yellow-700 rounded-lg p-4 text-yellow-300 text-sm mb-6">
                    PayPal is not configured. Please set your API keys in the .env file.
                </div>
            @endif

            <div x-show="!success">
                {{-- Amount --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-400 mb-1">Amount (USD)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-3 text-gray-400">$</span>
                        <input type="number" x-model="amount" min="{{ $minAmount }}" max="{{ $maxAmount }}" step="0.01"
                               class="w-full pl-8 pr-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:border-blue-500 focus:outline-none"
                               placeholder="0.00">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Min: ${{ number_format($minAmount, 2) }} — Max: ${{ number_format($maxAmount, 2) }}</p>
                </div>

                {{-- Description --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-400 mb-1">Description / Feature Request</label>
                    <textarea x-model="description" rows="3" maxlength="500"
                              class="w-full px-4 py-3 bg-gray-800 border border-gray-700 rounded-lg text-white focus:border-blue-500 focus:outline-none resize-none"
                              placeholder="Describe the feature you want or your message..."></textarea>
                    <p class="text-xs text-gray-500 mt-1" x-text="description.length + '/500'"></p>
                </div>

                {{-- Error --}}
                <div x-show="error" class="bg-red-900/50 border border-red-700 rounded-lg p-3 mb-4 text-red-300 text-sm" x-text="error"></div>

                {{-- PayPal Button --}}
                <div id="paypal-send-button" class="paypal-btn-container"></div>

                <p class="text-xs text-gray-500 text-center mt-4">
                    Secure payment processed by PayPal. We never store your card details.
                </p>
            </div>

            {{-- Success --}}
            <div x-show="success" class="text-center py-8">
                <div class="text-5xl mb-4">✅</div>
                <h2 class="text-xl font-bold mb-2">Payment Successful!</h2>
                <p class="text-gray-400 mb-6">Thank you for your payment. Your transaction has been recorded.</p>
                <a href="{{ url('/') }}" class="text-blue-400 hover:text-blue-300 underline">Return to dashboard</a>
            </div>
        </div>
    </div>

    @if($isConfigured)
    <script src="https://www.paypal.com/sdk/js?client-id={{ $clientId }}&currency=USD"></script>
    <script>
        function sendPayment() {
            return {
                amount: {{ $minAmount }},
                description: '',
                error: null,
                success: false,

                init() {
                    const self = this;
                    paypal.Buttons({
                        style: { layout: 'vertical', color: 'blue', shape: 'rect', label: 'pay' },
                        createOrder: function(data, actions) {
                            self.error = null;
                            if (!self.description.trim()) {
                                self.error = 'Please enter a description.';
                                return;
                            }
                            return fetch('{{ route("payment.create-custom-order") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ amount: self.amount, description: self.description })
                            })
                            .then(function(res) { return res.json(); })
                            .then(function(data) {
                                if (data.error) { self.error = data.error; throw new Error(data.error); }
                                return data.id;
                            });
                        },
                        onApprove: function(data) {
                            return fetch('{{ route("payment.capture") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify({ order_id: data.orderID })
                            })
                            .then(function(res) { return res.json(); })
                            .then(function(result) {
                                if (result.success) { self.success = true; }
                                else { self.error = 'Payment capture failed.'; }
                            });
                        },
                        onError: function(err) {
                            self.error = 'Payment failed. Please try again.';
                        }
                    }).render('#paypal-send-button');
                }
            };
        }
    </script>
    @endif
</body>
</html>
