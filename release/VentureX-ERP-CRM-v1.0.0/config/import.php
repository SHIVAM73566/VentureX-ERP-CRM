<?php

use App\Models\Customer;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Supplier;

return [

    /*
    |--------------------------------------------------------------------------
    | Supported File Types
    |--------------------------------------------------------------------------
    */
    'allowed_extensions' => ['csv', 'json', 'txt'],
    'max_file_size_kb' => 51200, // 50 MB

    /*
    |--------------------------------------------------------------------------
    | Batch Size
    |--------------------------------------------------------------------------
    */
    'batch_size' => 100,

    /*
    |--------------------------------------------------------------------------
    | Destination Modules
    |--------------------------------------------------------------------------
    */
    'destinations' => [
        'customers' => [
            'label' => 'Customers',
            'model' => Customer::class,
            'required_fields' => ['name'],
        ],
        'suppliers' => [
            'label' => 'Suppliers',
            'model' => Supplier::class,
            'required_fields' => ['name'],
        ],
        'products' => [
            'label' => 'Products',
            'model' => Product::class,
            'required_fields' => ['name'],
        ],
        'leads' => [
            'label' => 'Leads',
            'model' => Lead::class,
            'required_fields' => ['name'],
        ],
        'invoices' => [
            'label' => 'Invoices',
            'model' => Invoice::class,
            'required_fields' => ['invoice_number', 'customer_id'],
        ],
        'payments' => [
            'label' => 'Payments',
            'model' => Payment::class,
            'required_fields' => ['amount'],
        ],
        'employees' => [
            'label' => 'Employees',
            'model' => Employee::class,
            'required_fields' => ['first_name'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-Detection Keywords
    |--------------------------------------------------------------------------
    */
    'detection_keywords' => [
        'customers' => ['customer', 'client', 'buyer', 'account', 'contact_name', 'company_name'],
        'suppliers' => ['supplier', 'vendor', 'provider', 'distributor'],
        'products' => ['product', 'item', 'sku', 'inventory', 'stock', 'product_name'],
        'leads' => ['lead', 'prospect', 'pipeline', 'opportunity'],
        'invoices' => ['invoice', 'bill', 'amount_due', 'total_amount'],
        'payments' => ['payment', 'transaction', 'amount_paid', 'receipt'],
        'employees' => ['employee', 'staff', 'hire_date', 'department'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Duplicate Detection Fields
    |--------------------------------------------------------------------------
    */
    'unique_fields' => [
        'customers' => ['email', 'phone', 'customer_code', 'tax_id'],
        'suppliers' => ['email', 'phone', 'supplier_code', 'tax_id'],
        'products' => ['sku'],
        'leads' => ['email'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Validation Rules per Destination
    |--------------------------------------------------------------------------
    */
    'validation_rules' => [
        'customers' => [
            'email' => 'email',
            'phone' => 'phone',
            'tax_id' => 'tax_id',
        ],
        'suppliers' => [
            'email' => 'email',
            'phone' => 'phone',
            'tax_id' => 'tax_id',
        ],
        'products' => [
            'sku' => 'alphanumeric',
        ],
        'invoices' => [
            'invoice_number' => 'alphanumeric',
            'amount' => 'decimal',
        ],
    ],

];
