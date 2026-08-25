<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Cancelled</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-950 text-white min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full mx-4 text-center">
        <div class="bg-gray-900 rounded-2xl p-8 shadow-2xl">
            <div class="text-6xl mb-4">⚠️</div>
            <h1 class="text-2xl font-bold mb-4">Payment Cancelled</h1>
            <p class="text-gray-400 mb-8">Your payment was not processed. No charges were made.</p>
            <div class="space-y-3">
                <a href="{{ route('payment.index') }}" class="block w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg font-medium transition">Try Again</a>
                <a href="{{ url('/') }}" class="block w-full bg-gray-800 hover:bg-gray-700 text-white py-3 rounded-lg font-medium transition">Return to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
