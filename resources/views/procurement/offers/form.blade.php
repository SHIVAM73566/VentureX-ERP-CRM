<x-layouts.app
    :title="$offer->exists ? 'Edit Offer' : 'Capture Supplier Offer'"
    :breadcrumbs="[['label' => 'Procurement', 'url' => route('supplier-offers.index')], ['label' => 'Supplier Offers', 'url' => route('supplier-offers.index')], ['label' => $offer->exists ? 'Edit' : 'New']]">

    <div class="mx-auto max-w-4xl">
        @if (! $offer->exists)
            <div class="mb-6 card border-navy-200 bg-navy-50/50">
                <p class="text-sm text-navy-800">
                    <strong>Opal migration workflow:</strong> capture the supplier offer exactly as received. The system performs chemistry/grade
                    analysis, computes estimated metal value, and assigns a <strong>GREEN / YELLOW / RED</strong> quality status.
                    The AI never approves or rejects — a human always makes the final decision.
                </p>
            </div>
        @endif

        <form method="POST" action="{{ $offer->exists ? route('supplier-offers.update', $offer) : route('supplier-offers.store') }}" class="space-y-6">
            @csrf
            @if ($offer->exists)
                @method('PUT')
            @endif

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Supplier & Contact</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Supplier" name="supplier_id" :options="$suppliers->pluck('name', 'id')" :value="$offer->supplier_id" />
                    <x-form.input label="Source Email" name="source_email" type="email" :value="$offer->source_email" />
                    <x-form.input label="Contact Person" name="contact_person" :value="$offer->contact_person" />
                    <x-form.input label="Offer Date" name="offer_date" type="date" :value="$offer->offer_date?->format('Y-m-d') ?? now()->format('Y-m-d')" />
                    <x-form.input label="Validity Date" name="validity_date" type="date" :value="$offer->validity_date?->format('Y-m-d')" />
                </div>
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Material & Pricing</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.select label="Material Category *" name="material_category" :options="['copper' => 'Copper', 'aluminium' => 'Aluminium', 'stainless_steel' => 'Stainless Steel', 'brass' => 'Brass', 'iron_steel' => 'Iron / Steel (HMS)', 'mixed' => 'Mixed', 'other' => 'Other']" :value="$offer->material_category" required />
                    <x-form.input label="Grade" name="grade" :value="$offer->grade" placeholder="e.g. SS 304, Barley Grade" />
                    <x-form.input label="ISRI Grade" name="isri_grade" :value="$offer->isri_grade" />
                    <div class="sm:col-span-2">
                        <x-form.input label="Material Description" name="material_description" :value="$offer->material_description" placeholder="e.g. 99.9% copper barley scrap, loose, dry" />
                    </div>
                    <x-form.input label="Quantity (MT) *" name="quantity_mt" type="number" step="0.001" :value="$offer->quantity_mt" required />
                    <x-form.input label="Price / MT *" name="price_per_mt" type="number" step="0.01" :value="$offer->price_per_mt" required />
                    <x-form.input label="Currency" name="currency_code" :value="$offer->currency_code ?? 'USD'" />
                    <x-form.input label="Delivery Location" name="delivery_location" :value="$offer->delivery_location" />
                    <x-form.input label="Payment Terms" name="payment_terms" :value="$offer->payment_terms" />
                    <x-form.input label="Loading Terms" name="loading_terms" :value="$offer->loading_terms" />
                </div>
            </div>

            <div class="card space-y-4">
                <h2 class="text-lg font-bold text-ink-900">Chemistry (Spectro / COA)</h2>
                <p class="text-sm text-ink-400">Enter reported composition percentages. Used for grade matching and risk analysis.</p>
                <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                    <x-form.input label="Cu %" name="cu_percent" type="number" step="0.001" :value="$offer->cu_percent" />
                    <x-form.input label="Fe %" name="fe_percent" type="number" step="0.001" :value="$offer->fe_percent" />
                    <x-form.input label="Ni %" name="ni_percent" type="number" step="0.001" :value="$offer->ni_percent" />
                    <x-form.input label="Cr %" name="cr_percent" type="number" step="0.001" :value="$offer->cr_percent" />
                    <x-form.input label="Pb %" name="pb_percent" type="number" step="0.001" :value="$offer->pb_percent" />
                    <x-form.input label="Zn %" name="zn_percent" type="number" step="0.001" :value="$offer->zn_percent" />
                    <x-form.input label="Al %" name="al_percent" type="number" step="0.001" :value="$offer->al_percent" />
                    <x-form.input label="Mn %" name="mn_percent" type="number" step="0.001" :value="$offer->mn_percent" />
                    <x-form.input label="Mo %" name="mo_percent" type="number" step="0.001" :value="$offer->mo_percent" />
                    <x-form.input label="Other %" name="other_elements" :value="$offer->other_elements" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <x-form.input label="COA Number" name="coa_number" :value="$offer->coa_number" />
                    <x-form.input label="Spectro Report No." name="spectro_report_number" :value="$offer->spectro_report_number" />
                    <div class="sm:col-span-2">
                        <x-form.checkbox name="coa_available" label="COA Available" description="Mill / lab certificate of analysis received with the offer." :checked="$offer->coa_available" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('supplier-offers.index') }}" class="btn-secondary">Cancel</a>
                <button type="submit" class="btn-accent">{{ $offer->exists ? 'Save & Re-Analyse' : 'Capture & Analyse' }}</button>
            </div>
        </form>
    </div>
</x-layouts.app>
