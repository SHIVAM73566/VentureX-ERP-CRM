<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Company;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Models\User;
use App\Services\CompanyContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $company = CompanyContext::current() ?? Company::first();
        if (! $company) {
            $this->command?->warn('No company found; skipping demo data.');

            return;
        }

        CompanyContext::set($company->id);

        $users = User::ofCompany()->get();
        $owner = $users->where('email', 'purchase@jainmetal.example')->first() ?? $users->first();
        $salesUser = $users->where('email', 'sales@jainmetal.example')->first() ?? $users->first();
        $procurement = $users->where('email', 'procurement@jainmetal.example')->first() ?? $users->first();

        $suppliers = $this->seedSuppliers($company->id, $owner?->id);
        $customers = $this->seedCustomers($company->id, $salesUser?->id);
        $this->seedLeads($company->id, $salesUser?->id, $customers);
        $this->seedOpportunities($company->id, $customers, $salesUser?->id);
        $this->seedOffers($company->id, $suppliers, $procurement?->id);
        $this->seedActivities($company->id, $users);
    }

    protected function seedSuppliers(int $companyId, ?int $ownerId): array
    {
        $data = [
            ['S1', 'Green Metal Recycling LLC', 'Riyadh, Saudi Arabia', 'SA', 'METAL_RECYCLING@EXAMPLE.COM'],
            ['S2', 'Gulf Copper Traders', 'Dubai, UAE', 'AE', 'GULF_COPPER@EXAMPLE.COM'],
            ['S3', 'Tokyo Scrap Consortium', 'Tokyo, Japan', 'JP', 'TOKYO_SCRAP@EXAMPLE.COM'],
            ['S4', 'Hanover Steel Mills Export', 'Hamburg, Germany', 'DE', 'HANOVER_STEEL@EXAMPLE.COM'],
            ['S5', 'Korea Metal Supply Co', 'Busan, South Korea', 'KR', 'KOREA_METAL@EXAMPLE.COM'],
            ['S6', 'US Copper Scrap Exporters', 'Los Angeles, USA', 'US', 'US_COPPER@EXAMPLE.COM'],
        ];

        $suppliers = [];

        foreach ($data as [$code, $name, $city, $cc, $email]) {
            $suppliers[] = Supplier::create([
                'company_id' => $companyId,
                'supplier_code' => $code,
                'name' => $name,
                'city' => $city,
                'country_id' => Country::where('code', $cc)->first()?->id,
                'email' => strtolower($email),
                'status' => 'approved',
                'created_by' => $ownerId,
            ]);
        }

        return $suppliers;
    }

    protected function seedCustomers(int $companyId, ?int $userId): array
    {
        $data = [
            ['C1', 'Stainless Fabricators Ltd', 'Mumbai', 'IN'],
            ['C2', 'Precision Auto Components', 'Pune', 'IN'],
            ['C3', 'National Railways Foundry', 'Delhi', 'IN'],
            ['C4', 'Apex Copper Industries', 'Ahmedabad', 'IN'],
            ['C5', 'Surya Engineering Works', 'Rajkot', 'IN'],
        ];

        $customers = [];

        foreach ($data as [$code, $name, $city, $cc]) {
            $customers[] = Customer::create([
                'company_id' => $companyId,
                'customer_code' => $code,
                'name' => $name,
                'city' => $city,
                'country_id' => Country::where('code', $cc)->first()?->id,
                'currency_code' => 'INR',
                'status' => 'active',
                'created_by' => $userId,
            ]);
        }

        return $customers;
    }

    protected function seedLeads(int $companyId, ?int $userId, array $customers): void
    {
        $data = [
            ['Steel Bridge Fabricators', 'Rajesh Kumar', 'Copper Scrap 99.9%', 'Website', 245000, 'qualified'],
            ['Krishna Alloys Pvt Ltd', 'Meena Joshi', 'SS 304 Scrap', 'Referral', 385000, 'new'],
            ['Metro Industrial Suppliers', 'Arun Verma', 'Aluminium Scrap', 'Trade Show', 120000, 'contacted'],
            ['Shree Balaji Metals', 'Suresh Yadav', 'Brass Scrap', 'WhatsApp', 95000, 'proposal'],
            ['Western Casting Works', 'Deepak Nair', 'Copper Scrap', 'Cold Call', 410000, 'new'],
            ['Orbit Engineering Co', 'Priya Menon', 'SS 316 Scrap', 'Email', 160000, 'contacted'],
            ['Nirmal Industries', 'Ketan Shah', 'Mixed Scrap', 'LinkedIn', 78000, 'lost'],
        ];

        foreach ($data as $i => [$company, $contact, $product, $source, $value, $status]) {
            Lead::create([
                'company_id' => $companyId,
                'lead_number' => 'LEAD-'.str_pad((string) ($i + 1), 6, '0', STR_PAD_LEFT),
                'company_name' => $company,
                'contact_name' => $contact,
                'email' => strtolower(str_replace(' ', '.', $contact)).'@example.com',
                'phone' => '+91 98'.random_int(10000000, 99999999),
                'country_id' => Country::where('code', 'IN')->first()?->id,
                'source' => $source,
                'product_interest' => $product,
                'estimated_value' => $value,
                'currency_code' => 'INR',
                'status' => $status,
                'score' => $status === 'qualified' ? 75 : ($status === 'lost' ? 10 : random_int(20, 60)),
                'assigned_to' => $userId,
                'next_follow_up' => now()->addDays(($i % 6) + 1),
                'notes' => 'Generated demo lead.',
                'created_by' => $userId,
            ]);
        }
    }

    protected function seedOpportunities(int $companyId, array $customers, ?int $userId): void
    {
        $stages = [
            ['Stainless 304 supply contract', 'won', 0.95, 5200000, 1],
            ['Copper cathode annual contract', 'negotiation', 0.7, 8400000, 3],
            ['Aluminium scrap quarterly supply', 'proposal', 0.5, 3100000, 2],
            ['Brass scrap trial order', 'needs_analysis', 0.3, 900000, 4],
            ['SS 316 repeat order', 'qualification', 0.15, 1750000, 0],
        ];

        foreach ($stages as $i => [$name, $stage, $prob, $value, $customerIdx]) {
            Opportunity::create([
                'company_id' => $companyId,
                'name' => $name,
                'customer_id' => $customers[$customerIdx % count($customers)]->id ?? null,
                'expected_value' => $value,
                'currency_code' => 'INR',
                'stage' => $stage,
                'probability' => $prob,
                'expected_close_date' => now()->addDays(($i + 1) * 15),
                'assigned_to' => $userId,
                'source' => 'Sales pipeline',
                'created_by' => $userId,
            ]);
        }
    }

    protected function seedOffers(int $companyId, array $suppliers, ?int $userId): void
    {
        $offers = [
            [
                'supplier_idx' => 0, 'material' => 'Copper Scrap', 'grade' => 'Copper 99.9%',
                'isri' => 'Barley', 'qty' => 500, 'price' => 8900, 'currency' => 'USD',
                'cu' => 99.5, 'fe' => 0.2, 'quality' => 'GREEN', 'risk' => 'low', 'match' => 'match',
                'coa' => true, 'desc' => 'Bright and shiny copper scrap, clean, free from tin and solder',
            ],
            [
                'supplier_idx' => 1, 'material' => 'Copper Scrap', 'grade' => 'Copper 99.5%',
                'isri' => 'Barley', 'qty' => 300, 'price' => 9150, 'currency' => 'USD',
                'cu' => 99.2, 'fe' => 0.4, 'quality' => 'GREEN', 'risk' => 'low', 'match' => 'match',
                'coa' => true, 'desc' => 'Clean copper scrap with slight oxidation',
            ],
            [
                'supplier_idx' => 2, 'material' => 'Stainless Steel 304', 'grade' => 'SS 304',
                'isri' => 'Stainless', 'qty' => 750, 'price' => 1850, 'currency' => 'USD',
                'ni' => 8.1, 'cr' => 18.2, 'fe' => 70, 'quality' => 'GREEN', 'risk' => 'low', 'match' => 'match',
                'coa' => true, 'desc' => 'Stainless steel scrap 304 grade, clean coils and solids',
            ],
            [
                'supplier_idx' => 3, 'material' => 'Stainless Steel 304', 'grade' => 'SS 304',
                'isri' => 'Stainless', 'qty' => 1000, 'price' => 1720, 'currency' => 'USD',
                'ni' => 6.5, 'cr' => 15.8, 'quality' => 'RED', 'risk' => 'high', 'match' => 'mismatch',
                'coa' => false, 'desc' => 'Mixed stainless scrap, likely 304/430 blend, COA not provided',
            ],
            [
                'supplier_idx' => 4, 'material' => 'Aluminium Scrap', 'grade' => 'Aluminium 6061',
                'isri' => 'Tense', 'qty' => 200, 'price' => 2450, 'currency' => 'USD',
                'al' => 96.5, 'quality' => 'YELLOW', 'risk' => 'medium', 'match' => 'partial',
                'coa' => false, 'desc' => 'Aluminium scrap, composition partially verified, COA pending',
            ],
            [
                'supplier_idx' => 5, 'material' => 'Brass Scrap', 'grade' => 'Brass 60/40',
                'isri' => 'Drinkery', 'qty' => 150, 'price' => 5100, 'currency' => 'USD',
                'cu' => 60.2, 'zn' => 38.5, 'quality' => 'GREEN', 'risk' => 'low', 'match' => 'match',
                'coa' => true, 'desc' => 'Clean brass scrap, 60/40 composition confirmed by COA',
            ],
        ];

        foreach ($offers as $offer) {
            $supplier = $suppliers[$offer['supplier_idx']] ?? null;

            SupplierOffer::create([
                'company_id' => $companyId,
                'supplier_id' => $supplier?->id,
                'contact_person' => 'Sales Desk',
                'source_email' => $supplier?->email,
                'material_category' => str($offer['material'])->beforeLast(' ')->toString(),
                'material_description' => $offer['desc'],
                'grade' => $offer['grade'],
                'isri_grade' => $offer['isri'],
                'quantity_mt' => $offer['qty'],
                'price_per_mt' => $offer['price'],
                'currency_code' => $offer['currency'],
                'cu_percent' => $offer['cu'] ?? null,
                'fe_percent' => $offer['fe'] ?? null,
                'ni_percent' => $offer['ni'] ?? null,
                'cr_percent' => $offer['cr'] ?? null,
                'zn_percent' => $offer['zn'] ?? null,
                'al_percent' => $offer['al'] ?? null,
                'coa_number' => $offer['coa'] ? 'COA-'.strtoupper(Str::random(6)) : null,
                'coa_available' => $offer['coa'],
                'offer_date' => now()->subDays(random_int(1, 10)),
                'validity_date' => now()->addDays(30),
                'quality_status' => $offer['quality'],
                'risk_level' => $offer['risk'],
                'grade_match' => $offer['match'],
                'estimated_metal_value' => $offer['qty'] * $offer['price'],
                'buyer_action' => $offer['quality'] === 'RED' ? 'review' : 'contact',
                'created_by' => $userId,
            ]);
        }
    }

    protected function seedActivities(int $companyId, Collection $users): void
    {
        $leads = Lead::ofCompany()->get();

        foreach ($leads->take(6) as $i => $lead) {
            Activity::create([
                'company_id' => $companyId,
                'type' => ['call', 'meeting', 'email'][$i % 3],
                'subject_type' => $lead->getMorphClass(),
                'subject_id' => $lead->id,
                'title' => ['Initial call scheduled', 'Product demo', 'Follow-up email sent'][$i % 3],
                'due_at' => now()->addDays(($i % 5) + 1),
                'status' => 'open',
                'assigned_to' => $users->first()?->id,
                'created_by' => $users->first()?->id,
            ]);
        }
    }
}
