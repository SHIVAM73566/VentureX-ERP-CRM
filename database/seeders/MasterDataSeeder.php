<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Incoterm;
use App\Models\PaymentTerm;
use App\Models\TaxRate;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCountries();
        $this->seedCurrencies();
        $this->seedUnits();
        $this->seedTaxRates();
        $this->seedPaymentTerms();
        $this->seedIncoterms();
    }

    protected function seedCountries(): void
    {
        $countries = [
            ['India', 'IN', '+91', 'INR'],
            ['United States', 'US', '+1', 'USD'],
            ['United Kingdom', 'GB', '+44', 'GBP'],
            ['United Arab Emirates', 'AE', '+971', 'AED'],
            ['Germany', 'DE', '+49', 'EUR'],
            ['China', 'CN', '+86', 'CNY'],
            ['Japan', 'JP', '+81', 'JPY'],
            ['Singapore', 'SG', '+65', 'SGD'],
            ['Hong Kong', 'HK', '+852', 'HKD'],
            ['South Korea', 'KR', '+82', 'KRW'],
            ['Vietnam', 'VN', '+84', 'VND'],
            ['Thailand', 'TH', '+66', 'THB'],
            ['Indonesia', 'ID', '+62', 'IDR'],
            ['Malaysia', 'MY', '+60', 'MYR'],
            ['Australia', 'AU', '+61', 'AUD'],
            ['Canada', 'CA', '+1', 'CAD'],
            ['Netherlands', 'NL', '+31', 'EUR'],
            ['Italy', 'IT', '+39', 'EUR'],
            ['France', 'FR', '+33', 'EUR'],
            ['Belgium', 'BE', '+32', 'EUR'],
            ['Turkey', 'TR', '+90', 'TRY'],
            ['Brazil', 'BR', '+55', 'BRL'],
            ['Mexico', 'MX', '+52', 'MXN'],
            ['South Africa', 'ZA', '+27', 'ZAR'],
            ['Russia', 'RU', '+7', 'RUB'],
            ['Nigeria', 'NG', '+234', 'NGN'],
            ['Kenya', 'KE', '+254', 'KES'],
            ['Egypt', 'EG', '+20', 'EGP'],
            ['Saudi Arabia', 'SA', '+966', 'SAR'],
            ['Qatar', 'QA', '+974', 'QAR'],
            ['Kuwait', 'KW', '+965', 'KWD'],
            ['Bangladesh', 'BD', '+880', 'BDT'],
            ['Pakistan', 'PK', '+92', 'PKR'],
            ['Sri Lanka', 'LK', '+94', 'LKR'],
            ['Nepal', 'NP', '+977', 'NPR'],
            ['Taiwan', 'TW', '+886', 'TWD'],
            ['Spain', 'ES', '+34', 'EUR'],
            ['Portugal', 'PT', '+351', 'EUR'],
            ['Greece', 'GR', '+30', 'EUR'],
            ['Poland', 'PL', '+48', 'PLN'],
        ];

        foreach ($countries as [$name, $code, $phone, $currency]) {
            Country::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'phone_code' => $phone, 'currency_code' => $currency]
            );
        }
    }

    protected function seedCurrencies(): void
    {
        $currencies = [
            ['INR', 'Indian Rupee', '₹', 2, true, true],
            ['USD', 'US Dollar', '$', 2, false, true],
            ['EUR', 'Euro', '€', 2, false, true],
            ['GBP', 'British Pound', '£', 2, false, true],
            ['AED', 'UAE Dirham', 'د.إ', 2, false, true],
            ['CNY', 'Chinese Yuan', '¥', 2, false, true],
            ['JPY', 'Japanese Yen', '¥', 0, false, true],
            ['SGD', 'Singapore Dollar', 'S$', 2, false, true],
            ['HKD', 'Hong Kong Dollar', 'HK$', 2, false, true],
            ['SAR', 'Saudi Riyal', '﷼', 2, false, true],
            ['QAR', 'Qatari Riyal', '﷼', 2, false, true],
            ['KWD', 'Kuwaiti Dinar', 'د.ك', 3, false, true],
            ['AUD', 'Australian Dollar', 'A$', 2, false, true],
            ['CAD', 'Canadian Dollar', 'C$', 2, false, true],
        ];

        foreach ($currencies as [$code, $name, $symbol, $decimals, $base, $active]) {
            Currency::updateOrCreate(
                ['code' => $code],
                ['name' => $name, 'symbol' => $symbol, 'decimal_places' => $decimals, 'is_base' => $base, 'is_active' => $active]
            );
        }
    }

    protected function seedUnits(): void
    {
        $units = [
            ['Kilogram', 'kg', 'weight'],
            ['Metric Ton', 'MT', 'weight'],
            ['Gram', 'g', 'weight'],
            ['Pound', 'lb', 'weight'],
            ['Piece', 'pcs', 'quantity'],
            ['Number', 'no', 'quantity'],
            ['Box', 'bx', 'quantity'],
            ['Carton', 'ctn', 'quantity'],
            ['Roll', 'roll', 'length'],
            ['Meter', 'm', 'length'],
            ['Feet', 'ft', 'length'],
            ['Liter', 'L', 'volume'],
            ['Square Meter', 'sqm', 'area'],
            ['Container', 'container', 'logistics'],
            ['Pallet', 'pallet', 'logistics'],
            ['Kilowatt', 'kW', 'energy'],
        ];

        foreach ($units as [$name, $code, $category]) {
            Unit::updateOrCreate(['code' => $code], ['name' => $name, 'category' => $category]);
        }
    }

    protected function seedTaxRates(): void
    {
        $taxes = [
            ['Zero Rated', '0', 'sales', true],
            ['Standard 18% GST', '18', 'sales', true],
            ['Reduced 12% GST', '12', 'sales', true],
            ['Reduced 5% GST', '5', 'sales', true],
            ['Zero Rated Purchase', '0', 'purchase', true],
            ['Purchase GST 18%', '18', 'purchase', true],
            ['Purchase GST 12%', '12', 'purchase', true],
            ['Purchase GST 5%', '5', 'purchase', true],
        ];

        foreach ($taxes as [$name, $rate, $type, $active]) {
            TaxRate::updateOrCreate(
                ['name' => $name],
                ['code' => str($name)->slug()->upper()->toString(), 'rate' => $rate, 'type' => $type, 'is_active' => $active]
            );
        }
    }

    protected function seedPaymentTerms(): void
    {
        $terms = [
            ['Immediate', 'IMMEDIATE', 0, 'Payment due immediately'],
            ['Net 7', 'NET7', 7, 'Payment due within 7 days'],
            ['Net 15', 'NET15', 15, 'Payment due within 15 days'],
            ['Net 30', 'NET30', 30, 'Payment due within 30 days'],
            ['Net 45', 'NET45', 45, 'Payment due within 45 days'],
            ['Net 60', 'NET60', 60, 'Payment due within 60 days'],
            ['Net 90', 'NET90', 90, 'Payment due within 90 days'],
            ['50% Advance / 50% Against Documents', 'ADV50_DOC50', 0, '50% advance payment, 50% against shipping documents'],
            ['100% Advance', 'ADV100', 0, '100% advance payment'],
            ['Letter of Credit (LC)', 'LC', 0, 'Payment via letter of credit'],
            ['Document Against Payment (D/P)', 'DP', 0, 'Documents against payment at sight'],
            ['Document Against Acceptance (D/A)', 'DA', 0, 'Documents against acceptance'],
            ['On Delivery', 'ON_DELIVERY', 0, 'Payment due on delivery'],
        ];

        foreach ($terms as [$name, $code, $days, $description]) {
            PaymentTerm::updateOrCreate(['code' => $code], ['name' => $name, 'due_days' => $days, 'description' => $description]);
        }
    }

    protected function seedIncoterms(): void
    {
        $terms = [
            ['EXW', 'Ex Works', 'Seller delivers goods at their premises'],
            ['FCA', 'Free Carrier', 'Seller delivers to carrier nominated by buyer at specified place'],
            ['FAS', 'Free Alongside Ship', 'Seller delivers alongside ship at named port'],
            ['FOB', 'Free On Board', 'Seller delivers on board vessel at named port of shipment'],
            ['CFR', 'Cost and Freight', 'Seller pays freight to named destination port'],
            ['CIF', 'Cost, Insurance and Freight', 'Seller pays freight and insurance to named destination port'],
            ['CPT', 'Carriage Paid To', 'Seller pays carriage to named destination'],
            ['CIP', 'Carriage and Insurance Paid To', 'Seller pays carriage and insurance to named destination'],
            ['DAP', 'Delivered At Place', 'Seller delivers at named place, buyer unloads'],
            ['DPU', 'Delivered At Place Unloaded', 'Seller delivers and unloads at named place'],
            ['DDP', 'Delivered Duty Paid', 'Seller bears all risks and costs including duty to named destination'],
        ];

        foreach ($terms as [$code, $name, $description]) {
            Incoterm::updateOrCreate(['code' => $code], ['name' => $name, 'description' => $description]);
        }
    }
}
