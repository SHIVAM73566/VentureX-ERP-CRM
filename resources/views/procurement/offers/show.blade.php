<x-layouts.app
    :title="'Offer — '.($offer->material_category ?? 'Unknown').' ('.$offer->quality_status.')'"
    :breadcrumbs="[['label' => 'Procurement', 'url' => route('supplier-offers.index')], ['label' => 'Supplier Offers', 'url' => route('supplier-offers.index')], ['label' => '#'.$offer->id]]">

    <x-slot name="actions">
        @can('update', $offer)
            <a href="{{ route('supplier-offers.edit', $offer) }}" class="btn-secondary">Edit</a>
        @endcan
    </x-slot>

    @php
        $analysis = $offer->ai_analysis ?? [];
        $elements = \App\Services\Procurement\ScrapOfferProcessingService::GRADE_CHEMISTRY[$analysis['grade_key'] ?? ''] ?? [];
    @endphp

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6">
            <div class="card">
                <h2 class="mb-3 text-lg font-bold text-ink-900">Offer Summary</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt class="text-ink-400">Material</dt><dd class="text-right">{{ $offer->material_category }}</dd></div>
                    @if ($offer->material_description)<div class="flex justify-between"><dt class="text-ink-400">Description</dt><dd class="text-right">{{ $offer->material_description }}</dd></div>@endif
                    @if ($offer->grade)<div class="flex justify-between"><dt class="text-ink-400">Grade</dt><dd class="text-right">{{ $offer->grade }}</dd></div>@endif
                    @if ($offer->isri_grade)<div class="flex justify-between"><dt class="text-ink-400">ISRI</dt><dd class="text-right">{{ $offer->isri_grade }}</dd></div>@endif
                    <div class="flex justify-between"><dt class="text-ink-400">Quantity</dt><dd>{{ number_format((float) $offer->quantity_mt, 3) }} MT</dd></div>
                    <div class="flex justify-between"><dt class="text-ink-400">Price/MT</dt><dd>{{ number_format((float) $offer->price_per_mt, 2) }} {{ $offer->currency_code ?? 'USD' }}</dd></div>
                    <div class="flex justify-between border-t border-ink-100 pt-2"><dt class="font-semibold text-ink-700">Est. Metal Value</dt><dd class="font-bold text-ink-900">{{ $offer->estimated_metal_value ? number_format((float) $offer->estimated_metal_value, 2) : '—' }}</dd></div>
                </dl>
            </div>

            <div class="card space-y-3">
                <h2 class="text-lg font-bold text-ink-900">Supplier</h2>
                @if ($offer->supplier)
                    <a href="{{ route('suppliers.show', $offer->supplier) }}" class="block font-medium text-navy-600 hover:text-navy-500">{{ $offer->supplier->name }}</a>
                    <p class="text-sm text-ink-400">{{ $offer->supplier->contact_person ?? '' }} {{ $offer->supplier->email ? '• '.$offer->supplier->email : '' }}</p>
                @else
                    <p class="text-sm text-ink-600">{{ $offer->source_email ?? 'No supplier linked' }}</p>
                @endif
                @if ($offer->contact_person)<p class="text-sm text-ink-600">Contact: {{ $offer->contact_person }}</p>@endif
                @if ($offer->offer_date)<p class="text-sm text-ink-600">Offered: {{ $offer->offer_date->format('d M Y') }}</p>@endif
                @if ($offer->validity_date)<p class="text-sm text-ink-600">Valid until: {{ $offer->validity_date->format('d M Y') }}</p>@endif
            </div>
        </div>

        <div class="space-y-6 lg:col-span-2">
            <div class="card border-{{ $offer->quality_status === 'GREEN' ? 'emerald' : ($offer->quality_status === 'RED' ? 'red' : 'amber') }}-200">
                <h2 class="mb-4 flex items-center gap-2 text-lg font-bold text-ink-900">
                    <span class="badge-{{ $offer->quality_status === 'GREEN' ? 'green' : ($offer->quality_status === 'RED' ? 'red' : 'amber') }}">{{ $offer->quality_status }}</span>
                    AI Chemistry & Grade Analysis
                </h2>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="rounded-lg bg-ink-50 p-3">
                        <p class="text-xs font-semibold uppercase text-ink-400">Grade Match</p>
                        <p class="text-sm font-bold text-ink-800">{{ $analysis['grade_match'] ?? 'unknown' }}</p>
                    </div>
                    <div class="rounded-lg bg-ink-50 p-3">
                        <p class="text-xs font-semibold uppercase text-ink-400">Risk Level</p>
                        <p class="text-sm font-bold text-ink-800">{{ $analysis['risk_level'] ?? '—' }}</p>
                    </div>
                    <div class="rounded-lg bg-ink-50 p-3">
                        <p class="text-xs font-semibold uppercase text-ink-400">Matched Grade</p>
                        <p class="text-sm font-bold text-ink-800">{{ $analysis['grade_key'] ?? 'unknown' }}</p>
                    </div>
                </div>

                @if (! empty($analysis['issues']))
                    <div class="mt-4 space-y-1">
                        @foreach ($analysis['issues'] as $issue)
                            <p class="text-sm text-red-600">• {{ $issue }}</p>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 text-sm text-emerald-600">No issues flagged. Requires COA verification before final decision.</p>
                @endif

                <div class="mt-5">
                    <p class="mb-2 text-xs font-bold uppercase tracking-wide text-ink-400">Reported Chemistry vs Expected Range</p>
                    <div class="space-y-2">
                        @forelse (($analysis['reported_elements'] ?? []) as $element => $value)
                            @php
                                $range = $elements[strtoupper($element)] ?? null;
                                $inRange = $range && $value >= $range[0] && $value <= $range[1];
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="w-8 text-sm font-semibold uppercase text-ink-600">{{ $element }}</span>
                                <div class="h-2 flex-1 rounded-full bg-ink-100">
                                    <div class="h-2 rounded-full {{ $inRange ? 'bg-emerald-500' : 'bg-red-500' }}" style="width: {{ max(4, min(100, $value)) }}%"></div>
                                </div>
                                <span class="w-16 text-right text-sm text-ink-600">{{ $value }}%</span>
                                @if ($range)
                                    <span class="w-20 text-xs text-ink-400">{{ $range[0] }}–{{ $range[1] }}%</span>
                                @endif
                            </div>
                        @empty
                            <p class="text-sm text-ink-400">No chemistry reported for this offer.</p>
                        @endforelse
                    </div>
                </div>

                <p class="mt-5 text-xs text-ink-400">
                    <strong>Governance:</strong> this analysis is advisory. The AI does not approve or reject suppliers.
                    A human buyer must make the final decision.
                </p>
            </div>

            <div class="card">
                <h3 class="mb-3 text-sm font-bold uppercase tracking-wide text-ink-400">Offer Documents & Terms</h3>
                <dl class="grid gap-2 text-sm sm:grid-cols-2">
                    @if ($offer->coa_number)<div class="flex justify-between"><dt class="text-ink-400">COA Number</dt><dd>{{ $offer->coa_number }}</dd></div>@endif
                    @if ($offer->spectro_report_number)<div class="flex justify-between"><dt class="text-ink-400">Spectro Report</dt><dd>{{ $offer->spectro_report_number }}</dd></div>@endif
                    <div class="flex justify-between"><dt class="text-ink-400">COA Available</dt><dd>{{ $offer->coa_available ? 'Yes' : 'No' }}</dd></div>
                    @if ($offer->delivery_location)<div class="flex justify-between"><dt class="text-ink-400">Delivery</dt><dd>{{ $offer->delivery_location }}</dd></div>@endif
                    @if ($offer->payment_terms)<div class="flex justify-between"><dt class="text-ink-400">Payment</dt><dd>{{ $offer->payment_terms }}</dd></div>@endif
                    @if ($offer->loading_terms)<div class="flex justify-between"><dt class="text-ink-400">Loading</dt><dd>{{ $offer->loading_terms }}</dd></div>@endif
                </dl>
            </div>
        </div>
    </div>
</x-layouts.app>
