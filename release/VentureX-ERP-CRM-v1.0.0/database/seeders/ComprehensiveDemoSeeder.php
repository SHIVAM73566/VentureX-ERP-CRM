<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Account;
use App\Models\ApprovalAction;
use App\Models\ApprovalRequest;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Container;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\LandedCost;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\RFQ;
use App\Models\RFQItem;
use App\Models\RFQResponse;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Shipment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\TaxRate;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class ComprehensiveDemoSeeder extends Seeder
{
    private int $companyId;

    private int $branchId;

    private array $supplierIds = [];

    private array $customerIds = [];

    private array $productIds = [];

    private array $warehouseIds = [];

    private array $userIds = [];

    private array $accountIds = [];

    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['name' => 'Jain Metal Industries'],
            [
                'legal_name' => 'Jain Metal Industries Private Limited',
                'email' => 'admin@jainmetal.example',
                'is_active' => true,
            ]
        );
        $this->companyId = $company->id;

        $branch = Branch::firstOrCreate(
            ['company_id' => $this->companyId, 'code' => 'HQ'],
            ['name' => 'Head Office', 'city' => 'Jamnagar', 'state' => 'Gujarat', 'is_active' => true]
        );
        $this->branchId = $branch->id;

        $this->seedSuppliers();
        $this->seedContacts();
        $this->seedWarehouses();
        $this->seedProducts();
        $this->seedStockMovements();
        $this->seedPurchaseRequisitions();
        $this->seedRFQs();
        $this->seedPurchaseOrders();
        $this->seedQuotations();
        $this->seedSalesOrders();
        $this->seedInvoices();
        $this->seedPayments();
        $this->seedShipments();
        $this->seedContainers();
        $this->seedLandedCosts();
        $this->seedChartOfAccounts();
        $this->seedJournalEntries();
        $this->seedApprovals();
        $this->seedDocuments();
        $this->seedNotifications();

        $this->command?->info('Comprehensive demo data seeded successfully.');
    }

    private function getUsers(): array
    {
        if (empty($this->userIds)) {
            $users = User::where('company_id', $this->companyId)->pluck('id', 'email')->toArray();
            $this->userIds = $users;
        }

        return $this->userIds;
    }

    private function userId(string $email): int
    {
        return (int) ($this->getUsers()[$email] ?? 1);
    }

    private function randomUserId(): int
    {
        return (int) User::where('company_id', $this->companyId)->inRandomOrder()->first()?->id ?? 1;
    }

    // ── Contacts ──────────────────────────────────────────────────────────

    private function seedContacts(): void
    {
        $customers = Customer::where('company_id', $this->companyId)->get();
        foreach ($customers as $customer) {
            $this->customerIds[] = $customer->id;
            Contact::create([
                'company_id' => $this->companyId,
                'customer_id' => $customer->id,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'title' => fake()->randomElement(['Manager', 'Director', 'Purchase Head', 'CEO', 'VP Operations']),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'mobile' => fake()->phoneNumber(),
                'is_primary' => true,
                'is_active' => true,
            ]);
            Contact::create([
                'company_id' => $this->companyId,
                'customer_id' => $customer->id,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'title' => fake()->randomElement(['Accountant', 'Buyer', 'Quality Manager', 'Logistics Head']),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'is_primary' => false,
                'is_active' => true,
            ]);
        }

        $suppliers = Supplier::where('company_id', $this->companyId)->get();
        foreach ($suppliers as $supplier) {
            Contact::create([
                'company_id' => $this->companyId,
                'customer_id' => null,
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'title' => fake()->randomElement(['Sales Manager', 'Export Manager', 'Director', 'Account Manager']),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'is_primary' => true,
                'is_active' => true,
            ]);
        }

        $this->command?->info('  Contacts seeded');
    }

    // ── Warehouses ────────────────────────────────────────────────────────

    private function seedWarehouses(): void
    {
        $warehouses = [
            ['code' => 'WH-JAM', 'name' => 'Jamnagar Main Warehouse', 'location' => 'Jamnagar, Gujarat', 'capacity' => 5000, 'is_default' => true],
            ['code' => 'WH-MUM', 'name' => 'Mumbai Port Yard', 'location' => 'Nhava Sheva, Mumbai', 'capacity' => 3000, 'is_default' => false],
            ['code' => 'WH-DEL', 'name' => 'Delhi Distribution Center', 'location' => 'Faridabad, Haryana', 'capacity' => 2000, 'is_default' => false],
        ];

        foreach ($warehouses as $wh) {
            $model = Warehouse::create([
                'company_id' => $this->companyId,
                'code' => $wh['code'],
                'name' => $wh['name'],
                'location' => $wh['location'],
                'manager_id' => $this->randomUserId(),
                'capacity' => $wh['capacity'],
                'is_default' => $wh['is_default'],
                'status' => 'active',
            ]);
            $this->warehouseIds[] = $model->id;
        }

        $this->command?->info('  Warehouses seeded');
    }

    // ── Suppliers ──────────────────────────────────────────────────────

    private function seedSuppliers(): void
    {
        $existing = Supplier::where('company_id', $this->companyId)->count();
        if ($existing > 0) {
            $this->supplierIds = Supplier::where('company_id', $this->companyId)->pluck('id')->toArray();
            $this->command?->info('  Suppliers already exist ('.$existing.')');

            return;
        }

        $suppliers = [
            ['code' => 'SUP-001', 'name' => 'Green Metal Recycling LLC', 'city' => 'Riyadh', 'country_code' => 'SA'],
            ['code' => 'SUP-002', 'name' => 'Gulf Copper Traders', 'city' => 'Dubai', 'country_code' => 'AE'],
            ['code' => 'SUP-003', 'name' => 'Tokyo Scrap Consortium', 'city' => 'Tokyo', 'country_code' => 'JP'],
            ['code' => 'SUP-004', 'name' => 'Hanover Steel Mills Export', 'city' => 'Hamburg', 'country_code' => 'DE'],
            ['code' => 'SUP-005', 'name' => 'Korea Metal Supply Co', 'city' => 'Busan', 'country_code' => 'KR'],
            ['code' => 'SUP-006', 'name' => 'US Copper Scrap Exporters', 'city' => 'Los Angeles', 'country_code' => 'US'],
        ];

        foreach ($suppliers as $s) {
            $model = Supplier::create([
                'company_id' => $this->companyId,
                'supplier_code' => $s['code'],
                'name' => $s['name'],
                'city' => $s['city'],
                'country_id' => Country::where('code', $s['country_code'])->first()?->id,
                'email' => strtolower(str_replace(' ', '_', $s['name'])).'@example.com',
                'status' => 'approved',
                'created_by' => $this->randomUserId(),
            ]);
            $this->supplierIds[] = $model->id;
        }

        $this->command?->info('  Suppliers seeded');
    }

    // ── Products ──────────────────────────────────────────────────────────

    private function seedProducts(): void
    {
        $unitKg = Unit::where('code', 'kg')->first()?->id;
        $unitMt = Unit::where('code', 'mt')->first()?->id;
        $unitPcs = Unit::where('code', 'pcs')->first()?->id;
        $taxGst = TaxRate::where('code', 'GST_18')->first()?->id;

        $suppliers = Supplier::where('company_id', $this->companyId)->pluck('id')->toArray();

        $products = [
            ['sku' => 'COP-CU99', 'name' => 'Copper Cathode (Cu 99.99%)', 'category' => 'Copper', 'purchase_price' => 785000, 'selling_price' => 815000, 'reorder_level' => 50, 'unit_id' => $unitKg],
            ['sku' => 'COP-SCRP', 'name' => 'Copper Scrap #1 (Berry)', 'category' => 'Copper Scrap', 'purchase_price' => 720000, 'selling_price' => 755000, 'reorder_level' => 100, 'unit_id' => $unitKg],
            ['sku' => 'COP-BLND', 'name' => 'Copper Scrap #2 (Mixed)', 'category' => 'Copper Scrap', 'purchase_price' => 680000, 'selling_price' => 710000, 'reorder_level' => 100, 'unit_id' => $unitKg],
            ['sku' => 'STL-SS304', 'name' => 'Stainless Steel 304 Scrap', 'category' => 'Stainless Steel', 'purchase_price' => 185000, 'selling_price' => 198000, 'reorder_level' => 200, 'unit_id' => $unitKg],
            ['sku' => 'STL-SS316', 'name' => 'Stainless Steel 316 Scrap', 'category' => 'Stainless Steel', 'purchase_price' => 265000, 'selling_price' => 285000, 'reorder_level' => 100, 'unit_id' => $unitKg],
            ['sku' => 'ALU-INGOT', 'name' => 'Aluminium Ingots (99.7%)', 'category' => 'Aluminium', 'purchase_price' => 235000, 'selling_price' => 248000, 'reorder_level' => 100, 'unit_id' => $unitKg],
            ['sku' => 'ALU-SCRP', 'name' => 'Aluminium Scrap (Taint/Tabor)', 'category' => 'Aluminium Scrap', 'purchase_price' => 195000, 'selling_price' => 210000, 'reorder_level' => 150, 'unit_id' => $unitKg],
            ['sku' => 'BRASS-CU70', 'name' => 'Brass Scrap (Cu 70/30)', 'category' => 'Brass', 'purchase_price' => 520000, 'selling_price' => 545000, 'reorder_level' => 80, 'unit_id' => $unitKg],
            ['sku' => 'ZNC-INGOT', 'name' => 'Zinc Ingots (99.95%)', 'category' => 'Zinc', 'purchase_price' => 265000, 'selling_price' => 280000, 'reorder_level' => 100, 'unit_id' => $unitKg],
            ['sku' => 'LD-BATT', 'name' => 'Lead Battery Scrap', 'category' => 'Lead', 'purchase_price' => 175000, 'selling_price' => 190000, 'reorder_level' => 50, 'unit_id' => $unitKg],
            ['sku' => 'NIC-CATH', 'name' => 'Nickel Cathode', 'category' => 'Nickel', 'purchase_price' => 1650000, 'selling_price' => 1720000, 'reorder_level' => 20, 'unit_id' => $unitKg],
            ['sku' => 'SN-INGOT', 'name' => 'Tin Ingots (99.9%)', 'category' => 'Tin', 'purchase_price' => 2800000, 'selling_price' => 2950000, 'reorder_level' => 10, 'unit_id' => $unitKg],
            ['sku' => 'FE-MS', 'name' => 'Mild Steel Scrap', 'category' => 'Ferrous', 'purchase_price' => 48000, 'selling_price' => 55000, 'reorder_level' => 500, 'unit_id' => $unitKg],
            ['sku' => 'FE-HMS1', 'name' => 'Heavy Melting Steel #1', 'category' => 'Ferrous', 'purchase_price' => 52000, 'selling_price' => 58000, 'reorder_level' => 500, 'unit_id' => $unitKg],
            ['sku' => 'FE-TURN', 'name' => 'Steel Turnings', 'category' => 'Ferrous', 'purchase_price' => 38000, 'selling_price' => 45000, 'reorder_level' => 300, 'unit_id' => $unitKg],
            ['sku' => 'SEAL-ALU', 'name' => 'Sealed Units (Compressor)', 'category' => 'Specialty', 'purchase_price' => 145000, 'selling_price' => 165000, 'reorder_level' => 30, 'unit_id' => $unitPcs],
            ['sku' => 'RADI-ALU', 'name' => 'Aluminium Radiators (Clean)', 'category' => 'Specialty', 'purchase_price' => 210000, 'selling_price' => 230000, 'reorder_level' => 40, 'unit_id' => $unitKg],
            ['sku' => 'CBL-COP', 'name' => 'Copper Wire Harness (Insulated)', 'category' => 'Copper Scrap', 'purchase_price' => 580000, 'selling_price' => 620000, 'reorder_level' => 60, 'unit_id' => $unitKg],
            ['sku' => 'ALU-EXTR', 'name' => 'Aluminium Extrusion Scrap', 'category' => 'Aluminium Scrap', 'purchase_price' => 200000, 'selling_price' => 218000, 'reorder_level' => 100, 'unit_id' => $unitKg],
            ['sku' => 'ZNC-DC', 'name' => 'Zinc Die-Cast Scrap', 'category' => 'Zinc', 'purchase_price' => 220000, 'selling_price' => 240000, 'reorder_level' => 80, 'unit_id' => $unitKg],
        ];

        foreach ($products as $p) {
            $model = Product::create([
                'company_id' => $this->companyId,
                'sku' => $p['sku'],
                'name' => $p['name'],
                'category' => $p['category'],
                'description' => $p['name'].' - Premium quality '.strtolower($p['category']).' for industrial use',
                'unit_id' => $p['unit_id'],
                'purchase_price' => $p['purchase_price'],
                'selling_price' => $p['selling_price'],
                'tax_rate_id' => $taxGst,
                'reorder_level' => $p['reorder_level'],
                'supplier_id' => $suppliers[array_rand($suppliers)] ?? null,
                'status' => 'active',
                'created_by' => $this->randomUserId(),
            ]);
            $this->productIds[] = $model->id;
        }

        $this->command?->info('  Products seeded');
    }

    // ── Stock Movements ───────────────────────────────────────────────────

    private function seedStockMovements(): void
    {
        foreach ($this->productIds as $pid) {
            $warehouseId = $this->warehouseIds[array_rand($this->warehouseIds)];
            StockMovement::create([
                'company_id' => $this->companyId,
                'product_id' => $pid,
                'warehouse_id' => $warehouseId,
                'type' => 'in',
                'quantity' => fake()->randomFloat(2, 100, 2000),
                'unit_cost' => fake()->randomFloat(2, 30000, 800000),
                'note' => 'Initial stock from opening balance',
                'created_by' => $this->randomUserId(),
            ]);
        }

        foreach (array_slice($this->productIds, 0, 10) as $pid) {
            StockMovement::create([
                'company_id' => $this->companyId,
                'product_id' => $pid,
                'warehouse_id' => $this->warehouseIds[array_rand($this->warehouseIds)],
                'type' => 'out',
                'quantity' => fake()->randomFloat(2, 10, 200),
                'note' => 'Dispatched against Sales Order',
                'created_by' => $this->randomUserId(),
            ]);
        }

        $this->command?->info('  Stock movements seeded');
    }

    // ── Purchase Requisitions ─────────────────────────────────────────────

    private function seedPurchaseRequisitions(): void
    {
        $statuses = ['draft', 'pending_approval', 'approved', 'rejected', 'ordered'];
        $priorities = ['low', 'medium', 'high', 'critical'];

        for ($i = 0; $i < 8; $i++) {
            $status = $statuses[$i % count($statuses)];
            $pr = PurchaseRequisition::create([
                'company_id' => $this->companyId,
                'pr_number' => 'PR-'.date('Ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'requester_id' => $this->randomUserId(),
                'status' => $status,
                'priority' => $priorities[array_rand($priorities)],
                'required_date' => fake()->dateTimeBetween('+1 week', '+2 months'),
                'notes' => 'Urgent requirement for '.fake()->randomElement(['production', 'trading', 'export order']),
                'created_by' => $this->randomUserId(),
            ]);

            $itemCount = rand(2, 4);
            for ($j = 0; $j < $itemCount; $j++) {
                PurchaseRequisitionItem::create([
                    'purchase_requisition_id' => $pr->id,
                    'product_id' => $this->productIds[array_rand($this->productIds)],
                    'description' => fake()->words(3, true),
                    'quantity' => fake()->randomFloat(2, 5, 500),
                    'unit' => 'kg',
                ]);
            }
        }

        $this->command?->info('  Purchase requisitions seeded');
    }

    // ── RFQs ──────────────────────────────────────────────────────────────

    private function seedRFQs(): void
    {
        $suppliers = Supplier::where('company_id', $this->companyId)->pluck('id')->toArray();

        for ($i = 0; $i < 5; $i++) {
            $rfq = RFQ::create([
                'company_id' => $this->companyId,
                'rfq_number' => 'RFQ-'.date('Ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'title' => fake()->randomElement(['Copper Scrap Procurement', 'Steel Scrap Buy', 'Aluminium Order', 'Mixed Metal Purchase', 'Specialty Metals RFQ']),
                'description' => 'Request for quotation for '.fake()->randomElement(['bulk copper scrap', 'stainless steel', 'aluminium ingots', 'mixed ferrous']),
                'status' => ['draft', 'sent', 'open', 'awarded', 'cancelled'][$i % 5],
                'issued_at' => fake()->dateTimeBetween('-30 days', '-5 days'),
                'closes_at' => fake()->dateTimeBetween('+5 days', '+30 days'),
                'created_by' => $this->randomUserId(),
            ]);

            $itemCount = rand(2, 3);
            for ($j = 0; $j < $itemCount; $j++) {
                $item = RFQItem::create([
                    'rfq_id' => $rfq->id,
                    'product_id' => $this->productIds[array_rand($this->productIds)],
                    'description' => fake()->words(3, true),
                    'quantity' => fake()->randomFloat(2, 20, 500),
                    'unit' => 'kg',
                ]);
            }

            if (in_array($rfq->status, ['open', 'awarded', 'cancelled'])) {
                foreach (array_slice($suppliers, 0, rand(2, 4)) as $sid) {
                    RFQResponse::create([
                        'rfq_id' => $rfq->id,
                        'supplier_id' => $sid,
                        'amount' => fake()->randomFloat(2, 500000, 50000000),
                        'delivery_time_days' => rand(7, 45),
                        'valid_until' => fake()->dateTimeBetween('+10 days', '+60 days'),
                        'status' => $rfq->status === 'awarded' ? 'awarded' : 'submitted',
                        'notes' => 'Competitive pricing with quality assurance',
                    ]);
                }
            }
        }

        $this->command?->info('  RFQs seeded');
    }

    // ── Purchase Orders ───────────────────────────────────────────────────

    private function seedPurchaseOrders(): void
    {
        $suppliers = Supplier::where('company_id', $this->companyId)->get();

        for ($i = 0; $i < 8; $i++) {
            $supplier = $suppliers[$i % $suppliers->count()];
            $items = [];
            $subtotal = 0;

            $itemCount = rand(2, 5);
            for ($j = 0; $j < $itemCount; $j++) {
                $qty = fake()->randomFloat(2, 10, 300);
                $price = fake()->randomFloat(2, 40000, 800000);
                $taxRate = 18;
                $lineTotal = round($qty * $price * (1 + $taxRate / 100), 2);
                $subtotal += $lineTotal;
                $items[] = compact('qty', 'price', 'taxRate', 'lineTotal');
            }

            $statuses = ['draft', 'pending', 'approved', 'ordered', 'partially_received', 'received'];
            $status = $statuses[$i % count($statuses)];

            $po = PurchaseOrder::create([
                'company_id' => $this->companyId,
                'po_number' => 'PO-'.date('Ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'supplier_id' => $supplier->id,
                'status' => $status,
                'payment_status' => $i < 3 ? 'unpaid' : ($i < 6 ? 'partial' : 'paid'),
                'order_date' => fake()->dateTimeBetween('-45 days', '-5 days'),
                'expected_date' => fake()->dateTimeBetween('+5 days', '+30 days'),
                'subtotal' => $subtotal / 1.18,
                'tax' => $subtotal - ($subtotal / 1.18),
                'total' => $subtotal,
                'paid_amount' => $status === 'received' ? $subtotal : ($i < 6 ? $subtotal * 0.5 : 0),
                'payment_terms' => fake()->randomElement(['Net 30', 'Net 60', '100% Advance', 'LC at Sight']),
                'notes' => 'Bulk order for '.$supplier->name,
                'created_by' => $this->randomUserId(),
            ]);

            foreach ($items as $idx => $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'product_id' => $this->productIds[array_rand($this->productIds)],
                    'description' => fake()->words(3, true),
                    'quantity' => $item['qty'],
                    'unit' => 'kg',
                    'unit_price' => $item['price'],
                    'tax_rate' => $item['taxRate'],
                    'line_total' => $item['lineTotal'],
                    'sort' => $idx,
                ]);
            }
        }

        $this->command?->info('  Purchase orders seeded');
    }

    // ── Quotations ────────────────────────────────────────────────────────

    private function seedQuotations(): void
    {
        $customers = Customer::where('company_id', $this->companyId)->get();

        for ($i = 0; $i < 8; $i++) {
            $customer = $customers[$i % $customers->count()];
            $items = [];
            $subtotal = 0;

            $itemCount = rand(1, 4);
            for ($j = 0; $j < $itemCount; $j++) {
                $qty = fake()->randomFloat(2, 5, 200);
                $price = fake()->randomFloat(2, 50000, 900000);
                $discount = fake()->randomFloat(2, 0, 5);
                $taxRate = 18;
                $lineTotal = round(($qty * $price - $discount) * (1 + $taxRate / 100), 2);
                $subtotal += $lineTotal;
                $items[] = compact('qty', 'price', 'discount', 'taxRate', 'lineTotal');
            }

            $statuses = ['draft', 'sent', 'accepted', 'rejected', 'expired', 'converted'];
            $status = $statuses[$i % count($statuses)];

            $q = Quotation::create([
                'company_id' => $this->companyId,
                'quotation_number' => 'QUO-'.date('Ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'status' => $status,
                'currency_code' => 'INR',
                'subtotal' => $subtotal / 1.18,
                'tax' => $subtotal - ($subtotal / 1.18),
                'total' => $subtotal,
                'valid_until' => fake()->dateTimeBetween('+10 days', '+60 days'),
                'notes' => 'Competitive pricing for '.$customer->name,
                'terms' => 'Payment terms: Net 30 days. Delivery: Ex-works Jamnagar.',
                'created_by' => $this->randomUserId(),
            ]);

            foreach ($items as $idx => $item) {
                QuotationItem::create([
                    'quotation_id' => $q->id,
                    'product_id' => $this->productIds[array_rand($this->productIds)],
                    'description' => fake()->words(3, true),
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount' => $item['discount'],
                    'tax_rate' => $item['taxRate'],
                    'line_total' => $item['lineTotal'],
                    'sort' => $idx,
                ]);
            }
        }

        $this->command?->info('  Quotations seeded');
    }

    // ── Sales Orders ──────────────────────────────────────────────────────

    private function seedSalesOrders(): void
    {
        $customers = Customer::where('company_id', $this->companyId)->get();

        for ($i = 0; $i < 8; $i++) {
            $customer = $customers[$i % $customers->count()];
            $items = [];
            $subtotal = 0;

            $itemCount = rand(2, 5);
            for ($j = 0; $j < $itemCount; $j++) {
                $qty = fake()->randomFloat(2, 5, 200);
                $price = fake()->randomFloat(2, 50000, 900000);
                $discount = fake()->randomFloat(2, 0, 3);
                $taxRate = 18;
                $lineTotal = round(($qty * $price - $discount) * (1 + $taxRate / 100), 2);
                $subtotal += $lineTotal;
                $items[] = compact('qty', 'price', 'discount', 'taxRate', 'lineTotal');
            }

            $statuses = ['draft', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled'];
            $status = $statuses[$i % count($statuses)];

            $so = SalesOrder::create([
                'company_id' => $this->companyId,
                'order_number' => 'SO-'.date('Ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'status' => $status,
                'payment_status' => $i < 2 ? 'unpaid' : ($i < 5 ? 'partial' : 'paid'),
                'order_date' => fake()->dateTimeBetween('-30 days', '-2 days'),
                'delivery_date' => fake()->dateTimeBetween('+5 days', '+45 days'),
                'subtotal' => $subtotal / 1.18,
                'tax' => $subtotal - ($subtotal / 1.18),
                'total' => $subtotal,
                'paid_amount' => $status === 'completed' ? $subtotal : ($i < 5 ? $subtotal * 0.4 : 0),
                'notes' => 'Confirmed order for '.$customer->name,
                'created_by' => $this->randomUserId(),
            ]);

            foreach ($items as $idx => $item) {
                SalesOrderItem::create([
                    'sales_order_id' => $so->id,
                    'product_id' => $this->productIds[array_rand($this->productIds)],
                    'description' => fake()->words(3, true),
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount' => $item['discount'],
                    'tax_rate' => $item['taxRate'],
                    'line_total' => $item['lineTotal'],
                    'sort' => $idx,
                ]);
            }
        }

        $this->command?->info('  Sales orders seeded');
    }

    // ── Invoices ──────────────────────────────────────────────────────────

    private function seedInvoices(): void
    {
        $customers = Customer::where('company_id', $this->companyId)->get();
        $orders = SalesOrder::where('company_id', $this->companyId)->get();

        for ($i = 0; $i < 10; $i++) {
            $customer = $customers[$i % $customers->count()];
            $order = $orders->get($i % $orders->count());
            $items = [];
            $subtotal = 0;

            $itemCount = rand(1, 4);
            for ($j = 0; $j < $itemCount; $j++) {
                $qty = fake()->randomFloat(2, 5, 200);
                $price = fake()->randomFloat(2, 50000, 900000);
                $discount = fake()->randomFloat(2, 0, 3);
                $taxRate = 18;
                $lineTotal = round(($qty * $price - $discount) * (1 + $taxRate / 100), 2);
                $subtotal += $lineTotal;
                $items[] = compact('qty', 'price', 'discount', 'taxRate', 'lineTotal');
            }

            $statuses = ['draft', 'sent', 'partial', 'paid', 'overdue'];
            $status = $statuses[$i % count($statuses)];
            $paid = $status === 'paid' ? $subtotal : ($status === 'partial' ? $subtotal * 0.5 : 0);

            $inv = Invoice::create([
                'company_id' => $this->companyId,
                'invoice_number' => 'INV-'.date('Ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customer->id,
                'sales_order_id' => $order?->id,
                'status' => $status,
                'issue_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
                'due_date' => fake()->dateTimeBetween('+10 days', '+60 days'),
                'subtotal' => $subtotal / 1.18,
                'tax' => $subtotal - ($subtotal / 1.18),
                'total' => $subtotal,
                'paid_amount' => $paid,
                'notes' => 'Invoice for '.$customer->name,
                'created_by' => $this->randomUserId(),
            ]);

            foreach ($items as $idx => $item) {
                InvoiceItem::create([
                    'invoice_id' => $inv->id,
                    'product_id' => $this->productIds[array_rand($this->productIds)],
                    'description' => fake()->words(3, true),
                    'quantity' => $item['qty'],
                    'unit_price' => $item['price'],
                    'discount' => $item['discount'],
                    'tax_rate' => $item['taxRate'],
                    'line_total' => $item['lineTotal'],
                    'sort' => $idx,
                ]);
            }
        }

        $this->command?->info('  Invoices seeded');
    }

    // ── Payments ──────────────────────────────────────────────────────────

    private function seedPayments(): void
    {
        $invoices = Invoice::where('company_id', $this->companyId)
            ->whereIn('status', ['paid', 'partial', 'sent'])->get();

        $methods = ['bank', 'cheque', 'upi', 'card'];
        $i = 0;
        foreach ($invoices as $invoice) {
            if ($invoice->total <= 0) {
                continue;
            }
            $amount = $invoice->status === 'paid' ? (float) $invoice->total : (float) $invoice->total * rand(30, 60) / 100;
            Payment::create([
                'company_id' => $this->companyId,
                'payment_number' => 'PAY-'.date('Ymd').'-'.str_pad((string) (++$i), 4, '0', STR_PAD_LEFT),
                'customer_id' => $invoice->customer_id,
                'invoice_id' => $invoice->id,
                'payment_date' => fake()->dateTimeBetween('-20 days', '-1 day'),
                'amount' => round($amount, 2),
                'method' => $methods[array_rand($methods)],
                'reference' => 'REF-'.strtoupper(fake()->bothify('####')),
                'status' => 'completed',
                'created_by' => $this->randomUserId(),
            ]);
        }

        $this->command?->info('  Payments seeded');
    }

    // ── Shipments ─────────────────────────────────────────────────────────

    private function seedShipments(): void
    {
        $orders = SalesOrder::where('company_id', $this->companyId)->get();
        $poOrders = PurchaseOrder::where('company_id', $this->companyId)->get();
        $statuses = ['draft', 'scheduled', 'in_transit', 'customs', 'delivered', 'delayed'];

        for ($i = 0; $i < 5; $i++) {
            Shipment::create([
                'company_id' => $this->companyId,
                'shipment_number' => 'SHP-'.date('Ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                'sales_order_id' => $orders->get($i % $orders->count())?->id,
                'purchase_order_id' => $i < 3 ? $poOrders->get($i % $poOrders->count())?->id : null,
                'type' => $i < 3 ? 'inbound' : 'outbound',
                'carrier' => fake()->randomElement(['Maersk', 'MSC', 'CMA CGM', 'APL', 'Evergreen']),
                'tracking_number' => strtoupper(fake()->bothify('???######')),
                'mode' => fake()->randomElement(['sea', 'air', 'road']),
                'origin' => fake()->randomElement(['Jebel Ali, UAE', 'Rotterdam, Netherlands', 'Busan, South Korea', 'Jamnagar, India']),
                'status' => $statuses[$i % count($statuses)],
                'ship_date' => fake()->dateTimeBetween('-15 days', '-1 day'),
                'destination' => fake()->randomElement(['Nhava Sheva, India', 'Mundra, India', 'Chennai, India']),
                'weight' => fake()->randomFloat(2, 5000, 50000),
                'volume' => fake()->randomFloat(2, 20, 200),
                'created_by' => $this->randomUserId(),
            ]);
        }

        $this->command?->info('  Shipments seeded');
    }

    // ── Containers ────────────────────────────────────────────────────────

    private function seedContainers(): void
    {
        $shipments = Shipment::where('company_id', $this->companyId)->pluck('id')->toArray();

        $containers = [
            ['number' => 'MSKU7294831', 'type' => 'standard', 'size' => '40ft', 'status' => 'in_transit'],
            ['number' => 'TCLU8362945', 'type' => 'high_cube', 'size' => '40ft HC', 'status' => 'at_port'],
            ['number' => 'GESU5183746', 'type' => 'standard', 'size' => '20ft', 'status' => 'delivered'],
        ];

        foreach ($containers as $idx => $c) {
            Container::create([
                'company_id' => $this->companyId,
                'container_number' => $c['number'],
                'seal_number' => 'SEL-'.strtoupper(fake()->bothify('######')),
                'type' => $c['type'],
                'size' => $c['size'],
                'shipment_id' => $shipments[$idx] ?? null,
                'status' => $c['status'],
                'capacity' => $c['size'] === '20ft' ? 28000 : 30000,
            ]);
        }

        $this->command?->info('  Containers seeded');
    }

    // ── Landed Costs ──────────────────────────────────────────────────────

    private function seedLandedCosts(): void
    {
        $shipments = Shipment::where('company_id', $this->companyId)->pluck('id')->toArray();
        $types = ['freight', 'customs_duty', 'insurance', 'handling', 'inspection'];

        foreach (array_slice($shipments, 0, 3) as $sid) {
            foreach (array_slice($types, 0, rand(2, 4)) as $type) {
                LandedCost::create([
                    'company_id' => $this->companyId,
                    'shipment_id' => $sid,
                    'cost_type' => $type,
                    'description' => ucfirst(str_replace('_', ' ', $type)).' charges',
                    'amount' => fake()->randomFloat(2, 10000, 500000),
                    'currency' => 'INR',
                    'paid_to' => fake()->company(),
                    'status' => 'draft',
                    'created_by' => $this->randomUserId(),
                ]);
            }
        }

        $this->command?->info('  Landed costs seeded');
    }

    // ── Chart of Accounts ─────────────────────────────────────────────────

    private function seedChartOfAccounts(): void
    {
        $accounts = [
            ['code' => '1000', 'name' => 'Cash and Cash Equivalents', 'type' => 'asset'],
            ['code' => '1100', 'name' => 'Accounts Receivable', 'type' => 'asset'],
            ['code' => '1200', 'name' => 'Inventory', 'type' => 'asset'],
            ['code' => '1300', 'name' => 'Prepaid Expenses', 'type' => 'asset'],
            ['code' => '1500', 'name' => 'Fixed Assets - Equipment', 'type' => 'asset'],
            ['code' => '1600', 'name' => 'Accumulated Depreciation', 'type' => 'asset'],
            ['code' => '2000', 'name' => 'Accounts Payable', 'type' => 'liability'],
            ['code' => '2100', 'name' => 'GST Payable', 'type' => 'liability'],
            ['code' => '2200', 'name' => 'TDS Payable', 'type' => 'liability'],
            ['code' => '2300', 'name' => 'Short-term Loans', 'type' => 'liability'],
            ['code' => '2500', 'name' => 'Long-term Debt', 'type' => 'liability'],
            ['code' => '3000', 'name' => 'Owner Equity', 'type' => 'equity'],
            ['code' => '3100', 'name' => 'Retained Earnings', 'type' => 'equity'],
            ['code' => '4000', 'name' => 'Sales Revenue', 'type' => 'income'],
            ['code' => '4100', 'name' => 'Export Revenue', 'type' => 'income'],
            ['code' => '4200', 'name' => 'Scrap Sales', 'type' => 'income'],
            ['code' => '5000', 'name' => 'Cost of Goods Sold', 'type' => 'expense'],
            ['code' => '5100', 'name' => 'Purchase Cost', 'type' => 'expense'],
            ['code' => '5200', 'name' => 'Freight & Logistics', 'type' => 'expense'],
            ['code' => '5300', 'name' => 'Customs Duty', 'type' => 'expense'],
            ['code' => '6000', 'name' => 'Salaries & Wages', 'type' => 'expense'],
            ['code' => '6100', 'name' => 'Rent & Utilities', 'type' => 'expense'],
            ['code' => '6200', 'name' => 'Office Supplies', 'type' => 'expense'],
            ['code' => '6300', 'name' => 'Professional Services', 'type' => 'expense'],
            ['code' => '7000', 'name' => 'Bank Charges', 'type' => 'expense'],
            ['code' => '7100', 'name' => 'Interest Expense', 'type' => 'expense'],
        ];

        foreach ($accounts as $a) {
            $model = Account::create([
                'company_id' => $this->companyId,
                'code' => $a['code'],
                'name' => $a['name'],
                'type' => $a['type'],
                'description' => $a['name'].' account',
            ]);
            $this->accountIds[$a['code']] = $model->id;
        }

        $this->command?->info('  Chart of accounts seeded');
    }

    // ── Journal Entries ───────────────────────────────────────────────────

    private function seedJournalEntries(): void
    {
        $entries = [
            [
                'desc' => 'Opening balance - Cash',
                'lines' => [
                    ['account' => '1000', 'debit' => 5000000, 'credit' => 0],
                    ['account' => '3000', 'debit' => 0, 'credit' => 5000000],
                ],
            ],
            [
                'desc' => 'Purchase of Copper Scrap from Green Metal Recycling',
                'lines' => [
                    ['account' => '1200', 'debit' => 3500000, 'credit' => 0],
                    ['account' => '2000', 'debit' => 0, 'credit' => 3500000],
                ],
            ],
            [
                'desc' => 'Sale to Stainless Fabricators Pvt Ltd',
                'lines' => [
                    ['account' => '1100', 'debit' => 4200000, 'credit' => 0],
                    ['account' => '4000', 'debit' => 0, 'credit' => 4200000],
                ],
            ],
            [
                'desc' => 'Bank charges for LC processing',
                'lines' => [
                    ['account' => '7000', 'debit' => 25000, 'credit' => 0],
                    ['account' => '1000', 'debit' => 0, 'credit' => 25000],
                ],
            ],
            [
                'desc' => 'Monthly warehouse rent',
                'lines' => [
                    ['account' => '6100', 'debit' => 180000, 'credit' => 0],
                    ['account' => '1000', 'debit' => 0, 'credit' => 180000],
                ],
            ],
            [
                'desc' => 'Salary payment - January 2026',
                'lines' => [
                    ['account' => '6000', 'debit' => 2500000, 'credit' => 0],
                    ['account' => '1000', 'debit' => 0, 'credit' => 2500000],
                ],
            ],
            [
                'desc' => 'GST payment to government',
                'lines' => [
                    ['account' => '2100', 'debit' => 756000, 'credit' => 0],
                    ['account' => '1000', 'debit' => 0, 'credit' => 756000],
                ],
            ],
            [
                'desc' => 'Customs duty on import shipment',
                'lines' => [
                    ['account' => '5300', 'debit' => 425000, 'credit' => 0],
                    ['account' => '2000', 'debit' => 0, 'credit' => 425000],
                ],
            ],
        ];

        foreach ($entries as $idx => $e) {
            $je = JournalEntry::create([
                'company_id' => $this->companyId,
                'entry_number' => 'JE-'.date('Ymd').'-'.str_pad((string) ($idx + 1), 4, '0', STR_PAD_LEFT),
                'date' => fake()->dateTimeBetween('-30 days', '-1 day'),
                'description' => $e['desc'],
                'status' => 'posted',
                'created_by' => $this->randomUserId(),
            ]);

            foreach ($e['lines'] as $line) {
                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $this->accountIds[$line['account']],
                    'debit' => $line['debit'],
                    'credit' => $line['credit'],
                    'description' => $e['desc'],
                ]);
            }
        }

        $this->command?->info('  Journal entries seeded');
    }

    // ── Approvals ─────────────────────────────────────────────────────────

    private function seedApprovals(): void
    {
        $requests = [
            ['type' => 'purchase', 'title' => 'PO-20260816-0001 — Copper Cathode Bulk Order', 'risk' => 'high', 'status' => 'pending', 'desc' => 'Purchase order for 50 MT Copper Cathode from Green Metal Recycling, total value INR 39,250,000'],
            ['type' => 'export', 'title' => 'Customer Data Export — Sales Report Q1', 'risk' => 'medium', 'status' => 'pending', 'desc' => 'Export of customer sales data for Q1 2026 compliance audit'],
            ['type' => 'payment', 'title' => 'Payment Release — Gulf Copper Trading', 'risk' => 'high', 'status' => 'approved', 'desc' => 'Payment of INR 15,750,000 against PO-20260810-0002'],
            ['type' => 'ai_quota', 'title' => 'AI Quota Increase Request — Sales Team', 'risk' => 'low', 'status' => 'approved', 'desc' => 'Request to increase daily AI quota from 20 to 50 for sales department'],
            ['type' => 'user', 'title' => 'New User Registration — Logistics Department', 'risk' => 'low', 'status' => 'rejected', 'desc' => 'Registration request for new logistics coordinator with warehouse access'],
        ];

        $approverId = $this->userId('admin@jainmetal.example');

        foreach ($requests as $idx => $r) {
            $ar = ApprovalRequest::create([
                'company_id' => $this->companyId,
                'requester_id' => $this->randomUserId(),
                'request_type' => $r['type'],
                'title' => $r['title'],
                'description' => $r['desc'],
                'risk_level' => $r['risk'],
                'status' => $r['status'],
                'required_approvals' => $r['risk'] === 'high' ? 2 : 1,
                'current_approvals' => $r['status'] === 'approved' ? 1 : 0,
                'final_approver_id' => $r['status'] !== 'pending' ? $approverId : null,
                'finalized_at' => $r['status'] !== 'pending' ? now()->subDays(rand(1, 5)) : null,
            ]);

            if ($r['status'] !== 'pending') {
                ApprovalAction::create([
                    'approval_request_id' => $ar->id,
                    'approver_id' => $approverId,
                    'action' => $r['status'] === 'approved' ? 'approve' : 'reject',
                    'comment' => $r['status'] === 'approved' ? 'Approved after review' : 'Rejected — insufficient documentation',
                ]);
            }
        }

        $this->command?->info('  Approvals seeded');
    }

    // ── Documents ─────────────────────────────────────────────────────────

    private function seedDocuments(): void
    {
        $docs = [
            ['type' => 'certificate', 'title' => 'ISO 9001:2015 Certificate', 'mime' => 'application/pdf'],
            ['type' => 'certificate', 'title' => 'ISO 14001:2015 Environmental Certificate', 'mime' => 'application/pdf'],
            ['type' => 'invoice', 'title' => 'Invoice Template - Standard', 'mime' => 'application/pdf'],
            ['type' => 'contract', 'title' => 'Supplier Agreement - Green Metal Recycling', 'mime' => 'application/pdf'],
            ['type' => 'report', 'title' => 'Q1 2026 Financial Summary', 'mime' => 'application/pdf'],
            ['type' => 'compliance', 'title' => 'GST Registration Certificate', 'mime' => 'application/pdf'],
        ];

        foreach ($docs as $d) {
            Document::create([
                'company_id' => $this->companyId,
                'type' => $d['type'],
                'title' => $d['title'],
                'original_name' => strtolower(str_replace(' ', '-', $d['title'])).'.pdf',
                'storage_path' => 'documents/'.strtolower(str_replace(' ', '-', $d['title'])).'.pdf',
                'mime_type' => $d['mime'],
                'size' => fake()->numberBetween(50000, 5000000),
                'status' => fake()->randomElement(['new', 'reviewed', 'verified']),
                'scan_status' => 'clean',
                'uploaded_by' => $this->randomUserId(),
            ]);
        }

        $this->command?->info('  Documents seeded');
    }

    // ── Notifications ─────────────────────────────────────────────────────

    private function seedNotifications(): void
    {
        $users = User::where('company_id', $this->companyId)->pluck('id')->toArray();
        $notifications = [
            ['title' => 'New Purchase Order Created', 'body' => 'PO-20260816-0001 has been created and is pending approval', 'icon' => 'cart', 'color' => 'blue'],
            ['title' => 'Quotation Accepted', 'body' => 'Stainless Fabricators accepted quotation QUO-20260815-0003', 'icon' => 'check', 'color' => 'green'],
            ['title' => 'Invoice Overdue', 'body' => 'Invoice INV-20260801-0002 is 15 days overdue', 'icon' => 'alert', 'color' => 'red'],
            ['title' => 'Low Stock Alert', 'body' => 'Copper Scrap #1 (Berry) stock below reorder level', 'icon' => 'box', 'color' => 'amber'],
            ['title' => 'Shipment In Transit', 'body' => 'SHP-20260812-0001 is currently in transit', 'icon' => 'truck', 'color' => 'blue'],
            ['title' => 'AI Insight Available', 'body' => 'New procurement recommendation generated', 'icon' => 'sparkle', 'color' => 'purple'],
        ];

        foreach ($notifications as $n) {
            foreach (array_slice($users, 0, rand(1, 3)) as $uid) {
                Notification::create([
                    'user_id' => $uid,
                    'type' => 'App\\Notifications\\SystemNotification',
                    'title' => $n['title'],
                    'body' => $n['body'],
                    'icon' => $n['icon'],
                    'color' => $n['color'],
                    'read_at' => fake()->optional(0.4)->dateTimeBetween('-7 days', '-1 day'),
                ]);
            }
        }

        $this->command?->info('  Notifications seeded');
    }
}
