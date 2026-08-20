<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Product;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use App\Services\NumberGenerator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseRequisitionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', PurchaseRequisition::class);

        $requisitions = PurchaseRequisition::ofCompany()
            ->with('department', 'requester', 'items')
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->string('q');
                $q->where('pr_number', 'like', "%{$search}%");
            })
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('priority'), fn ($q) => $q->where('priority', $request->string('priority')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total' => PurchaseRequisition::ofCompany()->count(),
            'pending' => PurchaseRequisition::ofCompany()->where('status', 'pending_approval')->count(),
            'approved' => PurchaseRequisition::ofCompany()->where('status', 'approved')->count(),
            'ordered' => PurchaseRequisition::ofCompany()->where('status', 'ordered')->count(),
        ];

        return view('procurement.requisitions.index', compact('requisitions', 'summary'));
    }

    public function create(): View
    {
        $this->authorize('create', PurchaseRequisition::class);

        return view('procurement.requisitions.form', [
            'requisition' => new PurchaseRequisition,
            'departments' => Department::ofCompany()->orderBy('name')->get(),
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', PurchaseRequisition::class);

        $data = $request->validate($this->rules());

        try {
            $requisition = DB::transaction(function () use ($data, $request) {
                $requisition = PurchaseRequisition::create([
                    'company_id' => CompanyContext::id(),
                    'pr_number' => NumberGenerator::next('PR', 'purchase_requisitions'),
                    'requester_id' => $request->input('requester_id') ?? auth()->id(),
                    'created_by' => auth()->id(),
                    'status' => 'draft',
                    ...$data,
                ]);

                $this->syncItems($requisition, $data['items'] ?? []);

                return $requisition;
            });

            AuditLogger::created($requisition);

            return redirect()->route('procurement.requisitions.show', $requisition)->with('success', 'Purchase requisition created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create purchase requisition: '.$e->getMessage());
        }
    }

    public function show(PurchaseRequisition $requisition): View
    {
        $this->authorize('view', $requisition);

        if ((int) $requisition->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $requisition->load('department', 'requester', 'items.product', 'createdBy');

        return view('procurement.requisitions.show', compact('requisition'));
    }

    public function edit(PurchaseRequisition $requisition): View
    {
        $this->authorize('update', $requisition);

        if ((int) $requisition->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $requisition->load('items');

        return view('procurement.requisitions.form', [
            'requisition' => $requisition,
            'departments' => Department::ofCompany()->orderBy('name')->get(),
            'products' => Product::ofCompany()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        $this->authorize('update', $requisition);

        if ((int) $requisition->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $data = $request->validate($this->rules());

        try {
            DB::transaction(function () use ($data, $requisition) {
                $requisition->update($data);
                $this->syncItems($requisition, $data['items'] ?? []);
            });
            AuditLogger::updated($requisition);

            return redirect()->route('procurement.requisitions.show', $requisition)->with('success', 'Purchase requisition updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update purchase requisition: '.$e->getMessage());
        }
    }

    public function destroy(PurchaseRequisition $requisition): RedirectResponse
    {
        $this->authorize('delete', $requisition);

        if ((int) $requisition->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $requisition->delete();
        AuditLogger::deleted($requisition);

        return redirect()->route('procurement.requisitions.index')->with('success', 'Purchase requisition deleted.');
    }

    protected function syncItems(PurchaseRequisition $requisition, array $items): void
    {
        $requisition->items()->delete();
        foreach (array_values($items) as $sort => $item) {
            PurchaseRequisitionItem::create([
                'purchase_requisition_id' => $requisition->id,
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => (float) ($item['quantity'] ?? 1),
                'unit' => $item['unit'] ?? null,
                'notes' => $item['notes'] ?? null,
                'sort' => $sort,
            ]);
        }
    }

    protected function rules(): array
    {
        return [
            'department_id' => ['nullable', CompanyContext::existsInCompany('departments')],
            'requester_id' => ['nullable', 'exists:users,id', function ($attribute, $value, $fail) {
                $user = User::find($value);
                if (! $user || $user->company_id !== auth()->user()->company_id) {
                    $fail('The selected requester is not in your company.');
                }
            }],
            'status' => ['required', 'in:draft,pending_approval,approved,rejected,ordered'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'required_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['sometimes', 'array'],
            'items.*.product_id' => ['nullable', CompanyContext::existsInCompany('products')],
            'items.*.description' => ['required', 'string', 'max:512'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit' => ['nullable', 'string', 'max:24'],
            'items.*.notes' => ['nullable', 'string', 'max:512'],
        ];
    }
}
