<x-layouts.app title="Supplier Offers" :breadcrumbs="[['label' => 'Procurement'], ['label' => 'Supplier Offers']]">

    <x-slot name="actions">
        @can('create', App\Models\SupplierOffer::class)
            <a href="{{ route('supplier-offers.create') }}" class="btn-accent">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Capture Offer
            </a>
        @endcan
    </x-slot>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-dashboard.stat-card label="Total Offers" :value="$summary['total']" icon="box" color="blue" />
        <x-dashboard.stat-card label="GREEN — Complete" :value="$summary['green']" icon="badge" color="green" />
        <x-dashboard.stat-card label="YELLOW — Verify" :value="$summary['yellow']" icon="target" color="amber" />
        <x-dashboard.stat-card label="RED — Risk" :value="$summary['red']" icon="alert" color="red" />
        <x-dashboard.stat-card label="Avg Price/MT" :value="number_format($summary['avg_price'], 2)" icon="trending" color="violet" />
        <x-dashboard.stat-card label="Total Qty (MT)" :value="number_format($summary['total_qty'], 2)" icon="ship" color="gray" />
    </div>

    @if ($riskAlerts->isNotEmpty())
        <div class="mt-6 card border-red-200 bg-red-50/50">
            <h2 class="mb-2 flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-red-700">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                Risk Alerts — Chemistry / Grade Mismatch
            </h2>
            <div class="space-y-2">
                @foreach ($riskAlerts as $offer)
                    <div class="flex items-center justify-between rounded-lg bg-white p-3 text-sm">
                        <div>
                            <p class="font-medium text-ink-800">{{ $offer->material_category }} — {{ $offer->grade ?? 'grade not specified' }}</p>
                            <p class="text-xs text-ink-400">{{ $offer->supplier?->name }} • {{ number_format((float) $offer->quantity_mt, 2) }} MT @ {{ number_format((float) $offer->price_per_mt, 2) }}</p>
                        </div>
                        <a href="{{ route('supplier-offers.show', $offer) }}" class="font-medium text-red-600 hover:text-red-500">Review</a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="mt-6 mb-4 flex flex-col gap-3 sm:flex-row sm:items-center">
        <form method="GET" action="{{ route('supplier-offers.index') }}" class="flex flex-1 gap-2">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Search offers..." class="input max-w-md" />
            <select name="status" class="input max-w-[9rem]" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach (\App\Models\SupplierOffer::QUALITY_STATUSES as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
                @endforeach
            </select>
            <select name="material" class="input max-w-[10rem]" onchange="this.form.submit()">
                <option value="">All materials</option>
                @foreach ($materials as $material)
                    <option value="{{ $material }}" @selected(request('material') === $material)>{{ $material }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-secondary">Filter</button>
        </form>
    </div>

    <div class="card">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Supplier</th>
                        <th>Qty (MT)</th>
                        <th>Price/MT</th>
                        <th>Value</th>
                        <th>COA</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($offers as $offer)
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-800">{{ $offer->material_category }}</p>
                                <p class="text-xs text-ink-400">{{ $offer->grade ?? '—' }}{{ $offer->isri_grade ? ' (ISRI '.$offer->isri_grade.')' : '' }}</p>
                            </td>
                            <td>{{ $offer->supplier?->name ?? $offer->source_email ?? '—' }}</td>
                            <td>{{ number_format((float) $offer->quantity_mt, 2) }}</td>
                            <td>{{ number_format((float) $offer->price_per_mt, 2) }}</td>
                            <td>{{ $offer->estimated_metal_value ? number_format((float) $offer->estimated_metal_value, 2) : '—' }}</td>
                            <td>{{ $offer->coa_available ? 'Yes' : 'No' }}</td>
                            <td>
                                <span class="badge-{{ $offer->quality_status === 'GREEN' ? 'green' : ($offer->quality_status === 'RED' ? 'red' : 'amber') }}">{{ $offer->quality_status }}</span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('supplier-offers.show', $offer) }}" class="text-sm font-medium text-navy-600 hover:text-navy-500">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-ink-400">No offers captured yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $offers->links() }}</div>
</x-layouts.app>
