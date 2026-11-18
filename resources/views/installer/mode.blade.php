@extends('installer.layout')

@section('title', 'Installation Mode')

@section('content')
    <div class="card overflow-hidden">
        <div class="px-6 py-5 border-b border-ink-100 dark:border-ink-800">
            <h2 class="text-lg font-bold text-ink-900 dark:text-ink-50">Choose Installation Mode</h2>
            <p class="mt-1 text-sm text-ink-500 dark:text-ink-400">This choice controls whether sample data and public demo accounts are created.</p>
        </div>

        <form method="POST" action="{{ route('installer.mode.store') }}" class="p-6 space-y-5">
            @csrf

            <div class="grid gap-4 md:grid-cols-2">
                <label class="cursor-pointer rounded-xl border-2 p-5 transition {{ old('mode', $mode) === 'production' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-500/10' : 'border-ink-200 dark:border-ink-700' }}">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="mode" value="production" class="mt-1" @checked(old('mode', $mode) === 'production')>
                        <div>
                            <p class="text-sm font-bold text-ink-900 dark:text-ink-50">Production</p>
                            <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">Clean install for real business data. No sample records and no public demo logins are created.</p>
                        </div>
                    </div>
                </label>

                <label class="cursor-pointer rounded-xl border-2 p-5 transition {{ old('mode', $mode) === 'demo' ? 'border-indigo-500 bg-indigo-50/60 dark:bg-indigo-500/10' : 'border-ink-200 dark:border-ink-700' }}">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="mode" value="demo" class="mt-1" @checked(old('mode', $mode) === 'demo')>
                        <div>
                            <p class="text-sm font-bold text-ink-900 dark:text-ink-50">Demo</p>
                            <p class="mt-1 text-xs text-ink-500 dark:text-ink-400">Includes the full sample-data chain and demo users for evaluation only. Never use this mode for live data.</p>
                        </div>
                    </div>
                </label>
            </div>

            @error('mode')
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-400">{{ $message }}</div>
            @enderror

            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('installer.welcome') }}" class="btn-secondary">&larr; Back</a>
                <button type="submit" class="btn-accent">Continue &rarr;</button>
            </div>
        </form>
    </div>
@endsection
