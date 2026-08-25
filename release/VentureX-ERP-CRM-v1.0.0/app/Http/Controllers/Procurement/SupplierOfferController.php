<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\Procurement\ScrapOfferProcessingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SupplierOfferController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SupplierOffer::class);

        $offers = SupplierOffer::ofCompany()
            ->with('supplier', 'sourceDocument')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('material_description', 'like', "%{$search}%")
                        ->orWhere('grade', 'like', "%{$search}%")
                        ->orWhereHas('supplier', fn ($s) => $s->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('quality_status', $request->string('status')))
            ->when($request->filled('material'), fn ($q) => $q->where('material_category', $request->string('material')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => SupplierOffer::ofCompany()->count(),
            'green' => SupplierOffer::ofCompany()->where('quality_status', 'GREEN')->count(),
            'yellow' => SupplierOffer::ofCompany()->where('quality_status', 'YELLOW')->count(),
            'red' => SupplierOffer::ofCompany()->where('quality_status', 'RED')->count(),
            'missing_coa' => SupplierOffer::ofCompany()->where('coa_available', false)->count(),
            'total_qty' => (float) SupplierOffer::ofCompany()->sum('quantity_mt'),
            'avg_price' => (float) SupplierOffer::ofCompany()->avg('price_per_mt'),
            'lowest_price' => (float) SupplierOffer::ofCompany()->min('price_per_mt'),
        ];

        $riskAlerts = SupplierOffer::ofCompany()
            ->with('supplier')
            ->where('quality_status', 'RED')
            ->latest()
            ->get();

        $materials = SupplierOffer::ofCompany()->select('material_category')->distinct()->pluck('material_category')->filter()->values();

        return view('procurement.offers.index', compact('offers', 'summary', 'riskAlerts', 'materials'));
    }

    public function create(): View
    {
        $this->authorize('create', SupplierOffer::class);

        return view('procurement.offers.form', [
            'offer' => new SupplierOffer,
            'suppliers' => Supplier::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', SupplierOffer::class);

        $data = $request->validate($this->rules());

        try {
            $offer = DB::transaction(function () use ($data) {
                $offer = SupplierOffer::create([
                    ...$data,
                    'company_id' => CompanyContext::id(),
                    'created_by' => auth()->id(),
                ]);

                app(ScrapOfferProcessingService::class)->apply($offer);

                return $offer;
            });

            AuditLogger::log('create', 'supplier_offers', $offer, null, ['quality_status' => $offer->quality_status]);

            return redirect()->route('supplier-offers.show', $offer)
                ->with('success', 'Supplier offer captured and analysed. Status: '.$offer->quality_status.'. Final decision requires human review.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create supplier offer: '.$e->getMessage());
        }
    }

    public function show(SupplierOffer $offer): View
    {
        $this->authorize('view', $offer);

        if ((int) $offer->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $offer->load('supplier', 'sourceDocument');

        return view('procurement.offers.show', compact('offer'));
    }

    public function edit(SupplierOffer $offer): View
    {
        $this->authorize('update', $offer);

        if ((int) $offer->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        return view('procurement.offers.form', [
            'offer' => $offer,
            'suppliers' => Supplier::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SupplierOffer $offer): RedirectResponse
    {
        $this->authorize('update', $offer);

        if ((int) $offer->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $offer) {
                $offer->update($request->validate($this->rules()));
                app(ScrapOfferProcessingService::class)->apply($offer);
            });
            AuditLogger::updated($offer);

            return redirect()->route('supplier-offers.show', $offer)->with('success', 'Offer updated and re-analysed.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update supplier offer: '.$e->getMessage());
        }
    }

    public function destroy(SupplierOffer $offer): RedirectResponse
    {
        $this->authorize('delete', $offer);

        if ((int) $offer->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $offer->delete();
        AuditLogger::deleted($offer);

        return redirect()->route('supplier-offers.index')->with('success', 'Offer deleted.');
    }

    protected function rules(): array
    {
        return [
            'supplier_id' => ['nullable', CompanyContext::existsInCompany('suppliers')],
            'source_email' => ['nullable', 'email', 'max:255'],
            'contact_person' => ['nullable', 'string', 'max:128'],
            'material_category' => ['required', 'string', 'max:128'],
            'material_description' => ['nullable', 'string', 'max:512'],
            'grade' => ['nullable', 'string', 'max:64'],
            'isri_grade' => ['nullable', 'string', 'max:32'],
            'quantity_mt' => ['required', 'numeric', 'min:0'],
            'price_per_mt' => ['required', 'numeric', 'min:0'],
            'currency_code' => ['nullable', 'string', 'max:8'],
            'delivery_location' => ['nullable', 'string', 'max:255'],
            'payment_terms' => ['nullable', 'string', 'max:255'],
            'loading_terms' => ['nullable', 'string', 'max:255'],
            'cu_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'fe_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'ni_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'cr_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pb_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'zn_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'al_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'mn_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'mo_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'other_elements' => ['nullable', 'string', 'max:255'],
            'coa_number' => ['nullable', 'string', 'max:128'],
            'spectro_report_number' => ['nullable', 'string', 'max:128'],
            'coa_available' => ['sometimes', 'boolean'],
            'offer_date' => ['nullable', 'date'],
            'validity_date' => ['nullable', 'date'],
        ];
    }
}
