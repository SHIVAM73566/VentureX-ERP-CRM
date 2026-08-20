<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\TaxRate;
use App\Models\Unit;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Product::class);

        $products = Product::ofCompany()
            ->with('unit', 'taxRate', 'supplier')
            ->withCount('stockMovements')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')))
            ->when($request->boolean('low'), function ($q) {
                $q->whereNotNull('reorder_level')
                    ->whereRaw('(SELECT COALESCE(SUM(CASE WHEN type=\'in\' THEN quantity WHEN type=\'out\' THEN -quantity ELSE 0 END), 0) FROM stock_movements WHERE product_id = products.id) <= reorder_level');
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => Product::ofCompany()->count(),
            'active' => Product::ofCompany()->where('status', 'active')->count(),
            'low_stock' => Product::ofCompany()->whereNotNull('reorder_level')->get()->filter(fn (Product $p) => $p->availableStock() <= (float) $p->reorder_level)->count(),
            'stock_value' => (float) StockMovement::ofCompany()
                ->join('products as p', 'p.id', '=', 'stock_movements.product_id')
                ->whereNull('p.deleted_at')
                ->sum(DB::raw('(CASE WHEN stock_movements.type = \'in\' THEN stock_movements.quantity WHEN stock_movements.type = \'out\' THEN -stock_movements.quantity WHEN stock_movements.type = \'adjustment\' THEN stock_movements.quantity ELSE 0 END) * COALESCE(p.purchase_price, 0)')),
        ];

        $categories = Product::ofCompany()->select('category')->distinct()->pluck('category')->filter()->values();

        return view('inventory.products.index', compact('products', 'summary', 'categories'));
    }

    public function create(): View
    {
        $this->authorize('create', Product::class);

        return view('inventory.products.form', [
            'product' => new Product,
            'units' => Unit::orderBy('name')->get(),
            'taxRates' => TaxRate::orderBy('name')->get(),
            'suppliers' => Supplier::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Product::class);

        $data = $request->validate($this->rules());

        try {
            $product = DB::transaction(function () use ($data) {
                $product = Product::create([
                    'company_id' => CompanyContext::id(),
                    'created_by' => auth()->id(),
                    ...$data,
                ]);

                return $product;
            });

            AuditLogger::created($product);

            return redirect()->route('inventory.products.show', $product)->with('success', 'Product created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create product: '.$e->getMessage());
        }
    }

    public function show(Product $product): View
    {
        $this->authorize('view', $product);

        if ($product->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $product->load('unit', 'taxRate', 'supplier', 'stockMovements.warehouse', 'stockMovements.createdBy');

        return view('inventory.products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $this->authorize('update', $product);

        if ($product->company_id !== CompanyContext::id()) {
            abort(404);
        }

        return view('inventory.products.form', [
            'product' => $product,
            'units' => Unit::orderBy('name')->get(),
            'taxRates' => TaxRate::orderBy('name')->get(),
            'suppliers' => Supplier::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->authorize('update', $product);

        if ($product->company_id !== CompanyContext::id()) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $product) {
                $product->update($request->validate($this->rules()));
            });
            AuditLogger::updated($product);

            return redirect()->route('inventory.products.show', $product)->with('success', 'Product updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update product: '.$e->getMessage());
        }
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->authorize('delete', $product);

        if ($product->company_id !== CompanyContext::id()) {
            abort(404);
        }

        $product->delete();
        AuditLogger::deleted($product);

        return redirect()->route('inventory.products.index')->with('success', 'Product deleted.');
    }

    protected function rules(): array
    {
        return [
            'sku' => ['nullable', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:128'],
            'description' => ['nullable', 'string'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'selling_price' => ['nullable', 'numeric', 'min:0'],
            'tax_rate_id' => ['nullable', 'exists:tax_rates,id'],
            'reorder_level' => ['nullable', 'numeric', 'min:0'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'status' => ['required', 'in:active,inactive'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
