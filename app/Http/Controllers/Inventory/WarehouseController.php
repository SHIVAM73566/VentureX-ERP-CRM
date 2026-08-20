<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\AuditLogger;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Warehouse::class);

        $warehouses = Warehouse::ofCompany()
            ->with('manager')
            ->withCount('stockMovements')
            ->orderBy('name')
            ->paginate(15);

        return view('inventory.warehouses.index', compact('warehouses'));
    }

    public function create(): View
    {
        $this->authorize('create', Warehouse::class);

        return view('inventory.warehouses.form', [
            'warehouse' => new Warehouse,
            'managers' => User::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Warehouse::class);

        $data = $request->validate($this->rules());

        try {
            $warehouse = DB::transaction(function () use ($data, $request) {
                if ($request->boolean('is_default')) {
                    Warehouse::ofCompany()->update(['is_default' => false]);
                }

                return Warehouse::create([
                    'company_id' => CompanyContext::id(),
                    ...$data,
                    'is_default' => $request->boolean('is_default'),
                ]);
            });

            AuditLogger::created($warehouse);

            return redirect()->route('inventory.warehouses.index')->with('success', 'Warehouse created.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to create warehouse: '.$e->getMessage());
        }
    }

    public function edit(Warehouse $warehouse): View
    {
        $this->authorize('update', $warehouse);

        if ((int) $warehouse->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        return view('inventory.warehouses.form', [
            'warehouse' => $warehouse,
            'managers' => User::ofCompany()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('update', $warehouse);

        if ((int) $warehouse->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $data = $request->validate($this->rules());

        try {
            DB::transaction(function () use ($data, $request, $warehouse) {
                if ($request->boolean('is_default')) {
                    Warehouse::ofCompany()->where('id', '!=', $warehouse->id)->update(['is_default' => false]);
                }

                $warehouse->update([...$data, 'is_default' => $request->boolean('is_default')]);
            });
            AuditLogger::updated($warehouse);

            return redirect()->route('inventory.warehouses.index')->with('success', 'Warehouse updated.');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'Failed to update warehouse: '.$e->getMessage());
        }
    }

    public function destroy(Warehouse $warehouse): RedirectResponse
    {
        $this->authorize('delete', $warehouse);

        if ((int) $warehouse->company_id !== (int) CompanyContext::id()) {
            abort(404);
        }

        $warehouse->delete();
        AuditLogger::deleted($warehouse);

        return redirect()->route('inventory.warehouses.index')->with('success', 'Warehouse deleted.');
    }

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:32'],
            'name' => ['required', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'capacity' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
        ];
    }
}
