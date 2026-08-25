<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Container;
use App\Models\Customer;
use App\Models\PurchaseOrder;
use App\Models\SalesOrder;
use App\Models\Shipment;
use App\Models\Supplier;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\NumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Shipment::class);

        $shipments = Shipment::ofCompany()
            ->with('container', 'customer', 'supplier', 'salesOrder', 'purchaseOrder')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('shipment_number', 'like', "%{$search}%")
                        ->orWhere('tracking_number', 'like', "%{$search}%")
                        ->orWhere('origin', 'like', "%{$search}%")
                        ->orWhere('destination', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Shipment::ofCompany()->count(),
            'in_transit' => Shipment::ofCompany()->where('status', 'in_transit')->count(),
            'delivered' => Shipment::ofCompany()->where('status', 'delivered')->count(),
            'delayed' => Shipment::ofCompany()->where('status', 'delayed')->count(),
        ];

        return view('logistics.shipments.index', compact('shipments', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create', Shipment::class);

        return view('logistics.shipments.form', [
            'shipment' => new Shipment,
            'containers' => Container::ofCompany()->orderBy('container_number')->get(),
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'suppliers' => Supplier::ofCompany()->orderBy('name')->get(),
            'salesOrders' => SalesOrder::ofCompany()->whereIn('status', ['confirmed', 'processing', 'shipped'])->orderByDesc('id')->get(),
            'purchaseOrders' => PurchaseOrder::ofCompany()->whereIn('status', ['approved', 'ordered', 'partially_received'])->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Shipment::class);

        $data = $request->validate($this->rules());

        try {
            $shipment = DB::transaction(function () use ($data) {
                $shipment = Shipment::create([
                    'company_id' => CompanyContext::id(),
                    'shipment_number' => NumberGenerator::next('SHP', 'shipments'),
                    'created_by' => auth()->id(),
                    ...$data,
                ]);

                if ($shipment->salesOrder) {
                    $shipment->salesOrder->update(['status' => 'shipped']);
                }

                return $shipment;
            });

            AuditLogger::created($shipment);

            return redirect()->route('logistics.shipments.show', $shipment)->with('success', 'Shipment created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create shipment: '.$e->getMessage());
        }
    }

    public function show(Shipment $shipment): View
    {
        $this->authorize('view', $shipment);

        if ((int) $shipment->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $shipment->load('container', 'customer', 'supplier', 'salesOrder', 'purchaseOrder', 'createdBy');

        return view('logistics.shipments.show', compact('shipment'));
    }

    public function edit(Shipment $shipment): View
    {
        $this->authorize('update', $shipment);

        if ((int) $shipment->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        return view('logistics.shipments.form', [
            'shipment' => $shipment,
            'containers' => Container::ofCompany()->orderBy('container_number')->get(),
            'customers' => Customer::ofCompany()->orderBy('name')->get(),
            'suppliers' => Supplier::ofCompany()->orderBy('name')->get(),
            'salesOrders' => SalesOrder::ofCompany()->whereIn('status', ['confirmed', 'processing', 'shipped'])->orderByDesc('id')->get(),
            'purchaseOrders' => PurchaseOrder::ofCompany()->whereIn('status', ['approved', 'ordered', 'partially_received'])->orderByDesc('id')->get(),
        ]);
    }

    public function update(Request $request, Shipment $shipment): RedirectResponse
    {
        $this->authorize('update', $shipment);

        if ((int) $shipment->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $shipment) {
                $shipment->update($request->validate($this->rules()));
            });
            AuditLogger::updated($shipment);

            return redirect()->route('logistics.shipments.show', $shipment)->with('success', 'Shipment updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update shipment: '.$e->getMessage());
        }
    }

    public function destroy(Shipment $shipment): RedirectResponse
    {
        $this->authorize('delete', $shipment);

        if ((int) $shipment->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $shipment->delete();
        AuditLogger::deleted($shipment);

        return redirect()->route('logistics.shipments.index')->with('success', 'Shipment deleted.');
    }

    protected function rules(): array
    {
        return [
            'container_id' => ['nullable', CompanyContext::existsInCompany('containers')],
            'customer_id' => ['nullable', CompanyContext::existsInCompany('customers')],
            'supplier_id' => ['nullable', CompanyContext::existsInCompany('suppliers')],
            'sales_order_id' => ['nullable', CompanyContext::existsInCompany('sales_orders')],
            'purchase_order_id' => ['nullable', CompanyContext::existsInCompany('purchase_orders')],
            'type' => ['required', 'in:inbound,outbound'],
            'status' => ['required', 'in:draft,scheduled,in_transit,customs,delivered,delayed,cancelled'],
            'mode' => ['required', 'in:sea,air,road,rail'],
            'carrier' => ['nullable', 'string', 'max:255'],
            'tracking_number' => ['nullable', 'string', 'max:128'],
            'origin' => ['nullable', 'string', 'max:255'],
            'destination' => ['nullable', 'string', 'max:255'],
            'departure_date' => ['nullable', 'date'],
            'arrival_date' => ['nullable', 'date'],
            'shipped_at' => ['nullable', 'date'],
            'delivered_at' => ['nullable', 'date'],
            'weight' => ['nullable', 'numeric', 'min:0'],
            'volume' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
