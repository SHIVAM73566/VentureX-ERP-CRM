<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Installer') — VentureX ERP &amp; CRM</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <script>
        if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-ink-100 dark:bg-ink-950">
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="w-full max-w-3xl">
            <div class="mb-8 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-navy-900 text-xl font-bold text-white shadow-lg">V</div>
                <h1 class="mt-5 text-2xl font-bold text-ink-900 dark:text-ink-50">VentureX ERP &amp; CRM</h1>
                <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">Installation Wizard</p>
            </div>

            @include('installer.partials.steps', ['current' => $currentStep ?? 1])

            @yield('content')
        </div>
    </div>
    @yield('scripts')
</body>
</html>
