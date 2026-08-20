<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockMovementController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', StockMovement::class);

        $movements = StockMovement::ofCompany()
            ->with('product', 'warehouse', 'createdBy')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->string('type')))
            ->when($request->filled('warehouse_id'), fn ($q) => $q->where('warehouse_id', $request->integer('warehouse_id')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total_in' => (float) StockMovement::ofCompany()->where('type', 'in')->sum('quantity'),
            'total_out' => (float) StockMovement::ofCompany()->where('type', 'out')->sum('quantity'),
            'products' => Product::ofCompany()->count(),
            'adjustments' => StockMovement::ofCompany()->where('type', 'adjustment')->count(),
        ];

        return view('inventory.stock.index', [
            'movements' => $movements,
            'summary' => $summary,
            'warehouses' => Warehouse::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', StockMovement::class);

        return view('inventory.stock.form', [
            'movement' => new StockMovement,
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
            'warehouses' => Warehouse::ofCompany()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', StockMovement::class);

        $data = $request->validate($this->rules());

        $movement = StockMovement::create([
            'company_id' => CompanyContext::id(),
            'created_by' => auth()->id(),
            ...$data,
        ]);

        AuditLogger::log('create', 'stock_movements', $movement, null, [
            'product' => $movement->product?->name,
            'type' => $movement->type,
            'quantity' => $movement->quantity,
            'warehouse' => $movement->warehouse?->name,
        ]);

        return redirect()->route('inventory.stock.index')->with('success', 'Stock movement recorded.');
    }

    protected function rules(): array
    {
        return [
            'product_id' => ['required', CompanyContext::existsInCompany('products')],
            'warehouse_id' => ['required', CompanyContext::existsInCompany('warehouses')],
            'type' => ['required', 'in:in,out,transfer,adjustment'],
            'quantity' => ['required', 'numeric', 'min:0.0001'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'note' => ['nullable', 'string', 'max:512'],
        ];
    }
}
