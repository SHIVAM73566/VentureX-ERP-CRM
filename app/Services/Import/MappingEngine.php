<?php

namespace App\Services\Import;

use Illuminate\Support\Str;

class MappingEngine
{
    public function autoMapColumns(array $columns, string $destination): array
    {
        $erpFields = $this->getDestinationFields($destination);
        $mappings = [];

        foreach ($columns as $column) {
            $normalized = Str::snake(Str::lower(trim($column)));
            $normalized = preg_replace('/[^a-z0-9_]/', '_', $normalized);
            $normalized = preg_replace('/_+/', '_', $normalized);
            $normalized = trim($normalized, '_');

            $bestMatch = null;
            $confidence = 0;

            foreach ($erpFields as $field => $aliases) {
                $allNames = array_merge([$field], $aliases);
                foreach ($allNames as $alias) {
                    $normAlias = Str::snake(Str::lower(trim($alias)));
                    if ($normalized === $normAlias) {
                        $bestMatch = $field;
                        $confidence = 98;
                        break 2;
                    }
                    similar_text($normalized, $normAlias, $sim);
                    if ($sim > $confidence && $sim > 0.5) {
                        $confidence = (int) ($sim * 100);
                        $bestMatch = $field;
                    }
                }
            }

            $mappings[] = [
                'column' => $column,
                'field' => $bestMatch,
                'confidence' => $confidence,
                'status' => $confidence >= 70 ? 'mapped' : 'review',
            ];
        }

        return $mappings;
    }

    public function getDestinationFields(string $destination): array
    {
        return match ($destination) {
            'customers' => [
                'name' => ['customer_name', 'client_name', 'company_name', 'company', 'account'],
                'email' => ['email_address', 'mail', 'e_mail', 'contact_email'],
                'phone' => ['mobile', 'phone_number', 'telephone', 'tel', 'mobile_no'],
                'address' => ['street', 'street_address', 'billing_address'],
                'city' => ['town', 'locality'],
                'state' => ['province', 'region'],
                'country' => ['nation'],
                'postal_code' => ['zip', 'zip_code', 'pincode', 'pin'],
                'tax_id' => ['gst', 'gstin', 'vat', 'tin', 'pan', 'tax_number'],
                'customer_code' => ['code', 'customer_id', 'cust_code'],
                'contact_person' => ['contact', 'person', 'primary_contact'],
                'notes' => ['remark', 'comments', 'description'],
            ],
            'suppliers' => [
                'name' => ['supplier_name', 'vendor_name', 'company_name', 'company'],
                'email' => ['email_address', 'mail', 'contact_email'],
                'phone' => ['mobile', 'phone_number', 'telephone', 'tel'],
                'address' => ['street', 'street_address', 'billing_address'],
                'city' => ['town'],
                'state' => ['province', 'region'],
                'country' => ['nation'],
                'postal_code' => ['zip', 'zip_code', 'pincode'],
                'tax_id' => ['gst', 'gstin', 'vat', 'tin', 'pan'],
                'supplier_code' => ['code', 'supplier_id', 'supp_code'],
                'contact_person' => ['contact', 'person', 'primary_contact'],
                'notes' => ['remark', 'comments'],
            ],
            'products' => [
                'name' => ['product_name', 'item_name', 'item', 'description'],
                'sku' => ['product_code', 'item_code', 'code', 'barcode'],
                'sell_price' => ['price', 'selling_price', 'retail_price', 'unit_price', 'mrp'],
                'cost_price' => ['cost', 'purchase_price', 'buy_price', 'unit_cost'],
                'category' => ['category_name', 'product_category', 'type'],
                'unit' => ['unit_of_measure', 'uom', 'measurement'],
                'tax_rate' => ['tax', 'tax_percent', 'gst_rate'],
                'min_stock' => ['minimum_stock', 'reorder_level', 'reorder_point'],
                'max_stock' => ['maximum_stock', 'max_level'],
                'notes' => ['remark', 'description_2'],
            ],
            'leads' => [
                'company_name' => ['company', 'organization', 'business'],
                'contact_name' => ['name', 'contact', 'person', 'lead_name'],
                'email' => ['email_address', 'mail', 'e_mail'],
                'phone' => ['mobile', 'phone_number', 'telephone', 'tel'],
                'source' => ['lead_source', 'origin', 'how_found'],
                'status' => ['lead_status', 'state'],
                'value' => ['estimated_value', 'potential_value', 'deal_value'],
                'notes' => ['remark', 'comments', 'description'],
            ],
            'invoices' => [
                'invoice_number' => ['invoice_no', 'inv_number', 'inv_no', 'bill_number'],
                'customer_id' => ['customer', 'client_id', 'client', 'customer_name'],
                'invoice_date' => ['date', 'bill_date', 'issue_date'],
                'due_date' => ['payment_due', 'pay_by'],
                'total' => ['amount', 'total_amount', 'grand_total'],
                'status' => ['state', 'payment_status'],
                'notes' => ['remark', 'description'],
            ],
            'payments' => [
                'invoice_id' => ['invoice', 'invoice_number', 'bill_number'],
                'customer_id' => ['customer', 'client_id', 'client'],
                'amount' => ['payment_amount', 'paid_amount', 'total'],
                'payment_date' => ['date', 'paid_date', 'transaction_date'],
                'payment_method' => ['method', 'mode', 'type', 'pay_method'],
                'reference_number' => ['reference', 'ref', 'transaction_id'],
                'notes' => ['remark', 'description'],
            ],
            'employees' => [
                'name' => ['employee_name', 'full_name', 'staff_name'],
                'email' => ['email_address', 'mail', 'work_email'],
                'phone' => ['mobile', 'phone_number', 'telephone'],
                'department' => ['dept', 'department_name', 'division'],
                'position' => ['title', 'job_title', 'designation'],
                'salary' => ['pay', 'compensation', 'annual_salary'],
                'hire_date' => ['start_date', 'joining_date'],
                'status' => ['employment_status', 'state'],
            ],
            default => [
                'name' => ['name', 'title', 'label'],
                'email' => ['email', 'email_address', 'mail'],
                'phone' => ['phone', 'telephone', 'mobile'],
                'notes' => ['notes', 'description', 'comments', 'remark'],
            ],
        };
    }
}
