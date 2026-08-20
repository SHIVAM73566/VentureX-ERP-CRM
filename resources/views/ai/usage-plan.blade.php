<x-layouts.app title="AI Usage Plan" :breadcrumbs="[['label' => 'AI Center'], ['label' => 'Usage & Plans']]">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-ink-900">AI Usage & Plans</h1>
        <p class="mt-1 text-sm text-ink-500">Manage your AI usage and choose the plan that fits your business.</p>
    </div>

    {{-- Current Usage --}}
    <div class="card p-6 mb-8">
        <h2 class="text-lg font-semibold text-ink-900 mb-4">Current Usage</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-ink-700">Daily Usage</span>
                    <span class="text-sm text-ink-400">{{ $usage['daily_used'] ?? 0 }} / {{ $usage['daily_limit'] ?? 1 }}</span>
                </div>
                <div class="w-full bg-ink-100 rounded-full h-2.5">
                    @php $pct = ($usage['daily_limit'] ?? 1) > 0 ? min(100, (($usage['daily_used'] ?? 0) / ($usage['daily_limit'] ?? 1)) * 100) : 0; @endphp
                    <div class="h-2.5 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-navy-600') }}" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm font-medium text-ink-700">Weekly Usage</span>
                    <span class="text-sm text-ink-400">{{ $usage['weekly_used'] ?? 0 }} / {{ $usage['weekly_limit'] ?? 3 }}</span>
                </div>
                <div class="w-full bg-ink-100 rounded-full h-2.5">
                    @php $pct = ($usage['weekly_limit'] ?? 3) > 0 ? min(100, (($usage['weekly_used'] ?? 0) / ($usage['weekly_limit'] ?? 3)) * 100) : 0; @endphp
                    <div class="h-2.5 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-navy-600') }}" style="width: {{ $pct }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Plans --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($plans as $key => $plan)
        <div class="card p-6 border-2 {{ $key === 'free' ? 'border-navy-500' : 'border-transparent' }} hover:border-navy-300 transition">
            @if($key === 'free')
                <span class="inline-block bg-navy-100 text-navy-800 text-xs font-medium px-2.5 py-0.5 rounded mb-3">Current Plan</span>
            @endif
            <h3 class="text-lg font-semibold text-ink-900">{{ $plan['name'] }}</h3>
            <div class="mt-2 mb-4">
                <span class="text-3xl font-bold text-ink-900">${{ $plan['price'] }}</span>
                <span class="text-sm text-ink-400">/{{ $plan['period'] }}</span>
            </div>
            <ul class="space-y-2 mb-6">
                @foreach($plan['features'] as $feature)
                <li class="flex items-start text-sm text-ink-600">
                    <svg class="w-4 h-4 text-emerald-500 mr-1.5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    {{ $feature }}
                </li>
                @endforeach
            </ul>
            <div class="text-sm text-ink-400 mb-4">
                {{ $plan['daily_limit'] }} queries/day &middot; {{ $plan['weekly_limit'] }}/week
            </div>
            @if($key !== 'free')
            <button class="w-full bg-navy-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-navy-700 transition">
                Contact Support for Upgrade
            </button>
            @else
            <button class="w-full bg-ink-100 text-ink-400 px-4 py-2 rounded-lg text-sm font-medium cursor-not-allowed">
                Current Plan
            </button>
            @endif
        </div>
        @endforeach
    </div>

    {{-- Contact --}}
    <div class="mt-8 bg-ink-50 rounded-lg p-6 text-center">
        <p class="text-sm text-ink-500">Need a custom plan or have questions?</p>
        <p class="mt-1 text-sm font-medium text-ink-900">Contact support for upgrade options.</p>
    </div>

</x-layouts.app>
