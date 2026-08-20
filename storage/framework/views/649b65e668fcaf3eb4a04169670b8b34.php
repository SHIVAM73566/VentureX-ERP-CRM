<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'title' => null,
    'breadcrumbs' => [],
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'title' => null,
    'breadcrumbs' => [],
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $user = auth()->user();

    $nav = [
        'Overview' => [
            ['label' => 'Dashboard', 'icon' => 'layout', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard')],
        ],
        'CRM' => [
            ['label' => 'Customers', 'icon' => 'users', 'route' => 'customers.index', 'active' => request()->routeIs('customers.*')],
            ['label' => 'Contacts', 'icon' => 'user', 'route' => 'crm.contacts.index', 'active' => request()->routeIs('crm.contacts.*')],
            ['label' => 'Leads', 'icon' => 'target', 'route' => 'leads.index', 'active' => request()->routeIs('leads.*')],
            ['label' => 'Opportunities', 'icon' => 'trending', 'route' => 'opportunities.index', 'active' => request()->routeIs('opportunities.*')],
            ['label' => 'Activities', 'icon' => 'calendar', 'route' => 'crm.activities.index', 'active' => request()->routeIs('crm.activities.*')],
            ['label' => 'Sales Pipeline', 'icon' => 'columns', 'route' => 'pipeline.index', 'active' => request()->routeIs('pipeline.*')],
        ],
        'Sales' => [
            ['label' => 'Quotations', 'icon' => 'file', 'route' => 'sales.quotations.index', 'active' => request()->routeIs('sales.quotations.*')],
            ['label' => 'Sales Orders', 'icon' => 'clipboard', 'route' => 'sales.orders.index', 'active' => request()->routeIs('sales.orders.*')],
            ['label' => 'Invoices', 'icon' => 'receipt', 'route' => 'sales.invoices.index', 'active' => request()->routeIs('sales.invoices.*')],
            ['label' => 'Payments', 'icon' => 'credit', 'route' => 'sales.payments.index', 'active' => request()->routeIs('sales.payments.*')],
        ],
        'Purchase & Procurement' => [
            ['label' => 'Suppliers', 'icon' => 'truck', 'route' => 'suppliers.index', 'active' => request()->routeIs('suppliers.*')],
            ['label' => 'Supplier Offers', 'icon' => 'inbox', 'route' => 'supplier-offers.index', 'active' => request()->routeIs('supplier-offers.*')],
            ['label' => 'Purchase Requisitions', 'icon' => 'request', 'route' => 'procurement.requisitions.index', 'active' => request()->routeIs('procurement.requisitions.*')],
            ['label' => 'Purchase Orders', 'icon' => 'cart', 'route' => 'procurement.orders.index', 'active' => request()->routeIs('procurement.orders.*')],
            ['label' => 'RFQs', 'icon' => 'mail', 'route' => 'procurement.rfqs.index', 'active' => request()->routeIs('procurement.rfqs.*')],
        ],
        'Inventory' => [
            ['label' => 'Products', 'icon' => 'box', 'route' => 'inventory.products.index', 'active' => request()->routeIs('inventory.products.*')],
            ['label' => 'Warehouses', 'icon' => 'building', 'route' => 'inventory.warehouses.index', 'active' => request()->routeIs('inventory.warehouses.*')],
            ['label' => 'Stock', 'icon' => 'layers', 'route' => 'inventory.stock.index', 'active' => request()->routeIs('inventory.stock.*')],
        ],
        'Logistics' => [
            ['label' => 'Shipments', 'icon' => 'ship', 'route' => 'logistics.shipments.index', 'active' => request()->routeIs('logistics.shipments.*')],
            ['label' => 'Containers', 'icon' => 'box', 'route' => 'logistics.containers.index', 'active' => request()->routeIs('logistics.containers.*')],
            ['label' => 'Landed Cost', 'icon' => 'calculator', 'route' => 'logistics.landed-costs.index', 'active' => request()->routeIs('logistics.landed-costs.*')],
        ],
        'Finance' => [
            ['label' => 'Dashboard', 'icon' => 'chart', 'route' => 'finance.dashboard', 'active' => request()->routeIs('finance.dashboard')],
            ['label' => 'Chart of Accounts', 'icon' => 'book', 'route' => 'finance.accounts.index', 'active' => request()->routeIs('finance.accounts.*')],
            ['label' => 'Journal Entries', 'icon' => 'clipboard', 'route' => 'finance.journals.index', 'active' => request()->routeIs('finance.journals.*')],
            ['label' => 'Receivables', 'icon' => 'arrow-down', 'route' => 'finance.receivables', 'active' => request()->routeIs('finance.receivables')],
            ['label' => 'Payables', 'icon' => 'arrow-up', 'route' => 'finance.payables', 'active' => request()->routeIs('finance.payables')],
        ],
        'Documents' => [
            ['label' => 'Document Manager', 'icon' => 'folder', 'route' => 'admin.documents.index', 'active' => request()->routeIs('admin.documents.*'), 'visible' => $user?->can('configure')],
            ['label' => 'AI Document Reader', 'icon' => 'scan', 'route' => 'ai.document-reader', 'active' => request()->routeIs('ai.document-reader*')],
        ],
        'AI Center' => [
            ['label' => 'AI Assistant', 'icon' => 'spark', 'route' => 'ai.assistant', 'active' => request()->routeIs('ai.assistant')],
            ['label' => 'Business Copilot', 'icon' => 'cpu', 'route' => 'ai.copilot', 'active' => request()->routeIs('ai.copilot'), 'visible' => $user?->can('viewAny', App\Models\AiRun::class)],
            ['label' => 'AI Insights', 'icon' => 'bolt', 'route' => 'ai.usage-plan.index', 'active' => request()->routeIs('ai.usage-plan.*')],
            ['label' => 'AI Usage', 'icon' => 'chart', 'route' => 'ai.usage', 'active' => request()->routeIs('ai.usage'), 'visible' => $user?->can('viewAny', App\Models\AiRun::class)],
            ['label' => 'AI Skills', 'icon' => 'cpu', 'route' => 'admin.ai-skills.index', 'active' => request()->routeIs('admin.ai-skills.*')],
            ['label' => 'Procurement AI', 'icon' => 'scan', 'route' => 'ai.procurement', 'active' => request()->routeIs('ai.procurement*')],
        ],
        'Support' => [
            ['label' => 'Help Center', 'icon' => 'headset', 'route' => 'support.index', 'active' => request()->routeIs('support.index')],
            ['label' => 'My Tickets', 'icon' => 'ticket', 'route' => 'support.tickets.index', 'active' => request()->routeIs('support.tickets.*')],
            ['label' => 'Submit Ticket', 'icon' => 'plus', 'route' => 'support.tickets.create', 'active' => request()->routeIs('support.tickets.create')],
            ['label' => 'Documentation', 'icon' => 'book', 'route' => 'support.docs', 'active' => request()->routeIs('support.docs')],
            ['label' => 'FAQ', 'icon' => 'search', 'route' => 'support.faq', 'active' => request()->routeIs('support.faq')],
            ['label' => 'Contact Support', 'icon' => 'mail', 'route' => 'support.contact', 'active' => request()->routeIs('support.contact')],
            ['label' => 'Report Error', 'icon' => 'warning', 'route' => 'support.report-error', 'active' => request()->routeIs('support.report-error')],
        ],
        'Administration' => [
            ['label' => 'Companies', 'icon' => 'building', 'route' => 'admin.companies.index', 'active' => request()->routeIs('admin.companies.*'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'Users', 'icon' => 'user-plus', 'route' => 'admin.users.index', 'active' => request()->routeIs('admin.users.*'), 'visible' => $user?->can('viewAny', App\Models\User::class)],
            ['label' => 'Roles & Permissions', 'icon' => 'shield', 'route' => 'admin.roles.index', 'active' => request()->routeIs('admin.roles.*'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'Settings', 'icon' => 'gear', 'route' => 'admin.settings.index', 'active' => request()->routeIs('admin.settings.*'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'Security Center', 'icon' => 'shield', 'route' => 'security.dashboard', 'active' => request()->routeIs('security.*'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'Export Center', 'icon' => 'file', 'route' => 'admin.exports.index', 'active' => request()->routeIs('admin.exports.*'), 'visible' => $user?->can('export')],
            ['label' => 'Import Data', 'icon' => 'upload', 'route' => 'admin.imports.index', 'active' => request()->routeIs('admin.imports.*'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'Audit Logs', 'icon' => 'list', 'route' => 'admin.audit-logs.index', 'active' => request()->routeIs('admin.audit-logs.*'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'System Health', 'icon' => 'shield', 'route' => 'admin.system-health.index', 'active' => request()->routeIs('admin.system-health.*'), 'visible' => $user?->hasRole('super_admin')],
        ],
        'Super Admin' => [
            ['label' => 'Control Center', 'icon' => 'cpu', 'route' => 'admin.control-center.index', 'active' => request()->routeIs('admin.control-center.*'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'Support Desk', 'icon' => 'headset', 'route' => 'admin.support.index', 'active' => request()->routeIs('admin.support.*'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'Error Center', 'icon' => 'warning', 'route' => 'admin.errors.index', 'active' => request()->routeIs('admin.errors.*'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'Customers', 'icon' => 'users', 'route' => 'admin.control-center.customers', 'active' => request()->routeIs('admin.control-center.customers*'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'Announcements', 'icon' => 'bell', 'route' => 'admin.control-center.announcements', 'active' => request()->routeIs('admin.control-center.announcements'), 'visible' => $user?->hasRole('super_admin')],
            ['label' => 'Product Updates', 'icon' => 'arrow-up', 'route' => 'admin.control-center.updates', 'active' => request()->routeIs('admin.control-center.updates'), 'visible' => $user?->hasRole('super_admin')],
        ],
    ];

    $icons = [
        'layout' => 'M4 5a2 2 0 012-2h12a2 2 0 012 2v14a2 2 0 01-2 2H6a2 2 0 01-2-2V5z',
        'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'target' => 'M12 2a10 10 0 100 20 10 10 0 000-20zM12 6a6 6 0 100 12 6 6 0 000-12zm0 4a2 2 0 100 4 2 2 0 000-4z',
        'trending' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'columns' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v12a2 2 0 01-2 2h-2a2 2 0 01-2-2V6z',
        'file' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'clipboard' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        'receipt' => 'M9 14l6 0M9 10h6M4 5a2 2 0 012-2h12a2 2 0 012 2v16l-3-2-3 2-3-2-3 2-3-2V5z',
        'credit' => 'M3 10h18M7 15h2m-5 4h16a1 1 0 001-1V6a1 1 0 00-1-1H4a1 1 0 00-1 1v12a1 1 0 001 1z',
        'truck' => 'M5 8h11m-2 0V6a2 2 0 00-2-2H4a2 2 0 00-2 2v8a2 2 0 002 2h1m11 0a3 3 0 11-6 0 3 3 0 016 0zm0 0h4v-3l-2-2h-2m-2 5h2M3 4h1m4 0h1',
        'inbox' => 'M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4',
        'request' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2m-6 0a2 2 0 002 2h2a2 2 0 002-2m-6 0a2 2 0 012-2h2a2 2 0 012 2',
        'cart' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
        'mail' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'box' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'building' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'layers' => 'M12 2l9 5-9 5-9-5 9-5zm9 10l-9 5-9-5m18 5l-9 5-9-5',
        'ship' => 'M3 13l9 5 9-5M3 13l9-5 9 5M3 13v5l9 5 9-5v-5M12 3v5m0 0l2 1.5M12 8l-2 1.5',
        'calculator' => 'M9 7h6m-6 4h.01M12 11h.01M15 11h.01M9 15h.01M12 15h.01M15 15h.01M9 19h.01M12 19h.01M15 19h.01M4 3h16a1 1 0 011 1v16a1 1 0 01-1 1H4a1 1 0 01-1-1V4a1 1 0 011-1z',
        'chart' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'book' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'arrow-down' => 'M3 4h13M8 20V4m0 0l-5 5m5-5l5 5m8 9V9m0 0l-5 5m5-5l5 5',
        'arrow-up' => 'M3 20h13M8 4v16m0 0l-5-5m5 5l5-5m8-5v10m0 0l-5-5m5 5l5-5',
        'folder' => 'M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z',
        'scan' => 'M4 7V4h3m10 0h3v3M4 17v3h3m10 0h3v-3m-3-7a4 4 0 00-4-4 4 4 0 00-4 4 4 4 0 00-4 4 4 4 0 004 4 4 4 0 004-4 4 4 0 004-4z',
        'spark' => 'M5 3l.8 2.2L8 6l-2.2.8L5 9l-.8-2.2L2 6l2.2-.8L5 3zm11 0l.8 2.2L19 6l-2.2.8L16 9l-.8-2.2L13 6l2.2-.8L16 3zm-5 5l.8 2.2L14 11l-2.2.8L11 14l-.8-2.2L8 11l2.2-.8L11 8zm6 7l.8 2.2L20 18l-2.2.8L17 21l-.8-2.2L14 18l2.2-.8L17 15z',
        'cpu' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zm3-9h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4a1 1 0 011-1z',
        'user-plus' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a7 7 0 00-7 7h11',
        'shield' => 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3zm0 6v4m0 0a1 1 0 100 2 1 1 0 000-2z',
        'gear' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
        'list' => 'M4 6h16M4 10h16M4 14h16M4 18h16',
        'search' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        'bell' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9',
        'menu' => 'M4 6h16M4 12h16M4 18h16',
        'chevron' => 'M9 5l7 7-7 7',
        'moon' => 'M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z',
        'sun' => 'M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z',
        'logout' => 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1',
        'plus' => 'M12 4v16m8-8H4',
        'bolt' => 'M13 10V3L4 14h7v7l9-11h-7z',
        'briefcase' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'wallet' => 'M3 10h18M7 15h2m-5 4h16a1 1 0 001-1V6a1 1 0 00-1-1H4a1 1 0 00-1 1v12a1 1 0 001 1z',
        'badge' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 112 0v1m-2 1a2 2 0 002 2h2a2 2 0 002-2m-6 1v5m0 0l-2-2m2 2l2-2',
        'headset' => 'M18 9a6 6 0 00-12 0m12 0a6 6 0 01-6 6m-6-6a6 6 0 006 6m-6-6v4m6-4v4m-3 4v3m3-3v3M4 21h16a1 1 0 001-1v-5a1 1 0 00-1-1H4a1 1 0 00-1 1v5a1 1 0 001 1z',
        'ticket' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z',
        'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z',
    ];
?>

<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title><?php echo e($title ? $title.' â€” ' : ''); ?><?php echo e(config('app.name', 'MyERP')); ?></title>
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        }
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="h-full">
    <div x-data="sidebar" x-init="$store.theme.apply()" class="min-h-full">
        <div x-show="open" x-transition.opacity x-cloak @click="closeDrawer()" aria-hidden="true" class="fixed inset-0 z-30 bg-ink-950/40 lg:hidden"></div>

        <aside id="sidebar" x-ref="drawer" :class="[open ? 'translate-x-0' : '-translate-x-full', collapsed ? 'lg:w-16' : 'lg:w-64']" class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-navy-900 text-slate-300 transition-all duration-200 lg:translate-x-0" aria-label="Sidebar navigation">
            <div class="flex h-16 items-center gap-2.5 border-b border-white/10 px-4">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-accent-600 font-bold text-white">
                    <span :class="collapsed ? 'hidden' : ''">T</span><span :class="collapsed ? 'block' : 'hidden'">T</span>
                </div>
                <div :class="collapsed ? 'lg:hidden' : ''">
                    <p class="text-sm font-bold leading-tight text-white">VentureX ERP & CRM</p>
                    <p class="text-[11px] text-slate-400">Business Operating System</p>
                </div>
            </div>

            <nav class="flex-1 space-y-5 overflow-y-auto px-3 py-4">
                <?php $__currentLoopData = $nav; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $group => $items): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $visible = array_filter($items, fn ($i) => $i['visible'] ?? true);
                        if (! $visible) { continue; }
                    ?>
                    <div>
                        <p :class="collapsed ? 'lg:hidden' : ''" class="px-3 pb-1.5 text-[10px] font-bold uppercase tracking-widest text-slate-500"><?php echo e($group); ?></p>
                        <ul class="space-y-0.5">
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php if (! ($item['visible'] ?? true)) { continue; } ?>
                                <li>
                                    <?php if(($item['soon'] ?? false) || ! $item['route']): ?>
                                        <span class="group flex cursor-not-allowed items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] text-slate-500" title="Planned" aria-disabled="true">
                                            <svg class="h-4.5 w-4.5 shrink-0 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons[$item['icon']] ?? $icons['file']); ?>"/></svg>
                                            <span :class="collapsed ? 'lg:hidden' : ''" class="truncate"><?php echo e($item['label']); ?></span>
                                            <span :class="collapsed ? 'lg:hidden' : ''" class="ml-auto hidden rounded bg-white/5 px-1.5 py-0.5 text-[9px] font-semibold uppercase text-slate-400 group-hover:block">Soon</span>
                                        </span>
                                    <?php else: ?>
                                        <a href="<?php echo e(route($item['route'])); ?>" <?php if($item['active'] ?? false): ?> aria-current="page" <?php endif; ?> class="group flex items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] <?php echo e(($item['active'] ?? false) ? 'bg-accent-600/90 font-semibold text-white shadow-sm' : 'text-slate-300 hover:bg-white/5 hover:text-white'); ?>" title="<?php echo e($item['label']); ?>">
                                            <svg class="h-4.5 w-4.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons[$item['icon']] ?? $icons['file']); ?>"/></svg>
                                            <span :class="collapsed ? 'lg:hidden' : ''" class="truncate"><?php echo e($item['label']); ?></span>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </nav>

            <div class="border-t border-white/10 p-3">
                <div class="flex items-center gap-2.5 rounded-lg px-2 py-2">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-navy-700 text-xs font-bold text-white"><?php echo e($user?->initials() ?? '?'); ?></div>
                    <div :class="collapsed ? 'lg:hidden' : ''" class="min-w-0">
                        <p class="truncate text-[13px] font-semibold text-white"><?php echo e($user?->displayName()); ?></p>
                        <p class="truncate text-[11px] text-slate-400"><?php echo e($user?->roles->first()?->name); ?></p>
                    </div>
                </div>
                <a href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" title="Sign out" class="mt-2 flex items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] text-slate-400 hover:bg-white/5 hover:text-white">
                    <svg class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['logout']); ?>"/></svg>
                    <span :class="collapsed ? 'lg:hidden' : ''">Sign out</span>
                </a>
                <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="hidden"><?php echo csrf_field(); ?></form>
            </div>
        </aside>

        <div :class="collapsed ? 'lg:pl-16' : 'lg:pl-64'" class="flex min-h-screen w-full min-w-0 flex-col transition-all duration-200">
            <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-ink-200 bg-white/95 px-4 backdrop-blur lg:px-6 dark:border-ink-800 dark:bg-ink-900/95">
                <button @click="mobileToggle()" x-ref="menuButton" class="rounded-lg p-2 text-ink-500 hover:bg-ink-100 lg:hidden dark:text-ink-400 dark:hover:bg-ink-800" aria-label="Open navigation menu" :aria-expanded="open" aria-controls="sidebar">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="<?php echo e($icons['menu']); ?>"/></svg>
                </button>
                <button @click="toggle()" class="hidden rounded-lg p-2 text-ink-500 hover:bg-ink-100 lg:block dark:text-ink-400 dark:hover:bg-ink-800" :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'" aria-label="Toggle sidebar" :aria-expanded="!collapsed">
                    <svg x-show="!collapsed" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 18l-6-6 6-6"/></svg>
                    <svg x-show="collapsed" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>

                <form action="<?php echo e(route('search')); ?>" method="GET" class="relative hidden max-w-md flex-1 md:block">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-400 dark:text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['search']); ?>"/></svg>
                    <input type="search" name="q" placeholder="Search customers, suppliers, leads, offersâ€¦" class="w-full rounded-lg border border-ink-200 bg-ink-50 py-2 pl-9 pr-3 text-sm text-ink-700 placeholder-ink-400 focus:border-navy-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-navy-500/20 dark:border-ink-700 dark:bg-ink-800 dark:text-ink-200 dark:placeholder-ink-500 dark:focus:border-navy-400 dark:focus:bg-ink-900 dark:focus:ring-navy-400/30">
                </form>

                <div class="ml-auto flex items-center gap-1">
                    <button @click="$store.theme.toggle()" class="rounded-lg p-2 text-ink-500 hover:bg-ink-100 dark:text-ink-400 dark:hover:bg-ink-800" title="Toggle theme" aria-label="Toggle theme">
                        <svg x-show="!$store.theme.dark" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['moon']); ?>"/></svg>
                        <svg x-show="$store.theme.dark" x-cloak class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['sun']); ?>"/></svg>
                    </button>

                    <div x-data="dropdown" class="relative">
                        <button @click="toggle()" @click.outside="close()" class="relative rounded-lg p-2 text-ink-500 hover:bg-ink-100 dark:text-ink-400 dark:hover:bg-ink-800" aria-label="Notifications" aria-haspopup="menu" :aria-expanded="open">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['bell']); ?>"/></svg>
                            <span x-show="false" class="absolute right-1.5 top-1.5 h-2 w-2 rounded-full bg-red-500"></span>
                        </button>
                    </div>

                    <div x-data="dropdown" class="relative">
                        <button @click="toggle()" @click.outside="close()" class="flex items-center gap-2 rounded-lg p-1.5 hover:bg-ink-100 dark:hover:bg-ink-800" aria-label="Account menu" aria-haspopup="menu" :aria-expanded="open">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-navy-800 text-xs font-bold text-white"><?php echo e($user?->initials() ?? '?'); ?></span>
                            <span class="hidden text-sm font-medium text-ink-700 dark:text-ink-200 md:block"><?php echo e($user?->displayName()); ?></span>
                            <svg class="h-4 w-4 text-ink-400 dark:text-ink-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['chevron']); ?>"/></svg>
                        </button>
                        <div x-show="open" x-cloak x-transition @click.outside="close()" class="absolute right-0 mt-2 w-56 rounded-xl border border-ink-200 bg-white p-1.5 shadow-lg dark:border-ink-700 dark:bg-ink-800">
                            <?php if($user?->hasRole('super_admin') || $user?->can('configure')): ?>
                                <a href="<?php echo e(route('admin.settings.index')); ?>" class="block rounded-lg px-3 py-2 text-sm text-ink-700 hover:bg-ink-50 dark:text-ink-200 dark:hover:bg-ink-700">Settings</a>
                            <?php endif; ?>
                            <a href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="block rounded-lg px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-500/10">Sign out</a>
                        </div>
                    </div>
                </div>
            </header>

            <?php if($breadcrumbs): ?>
                <nav class="flex items-center gap-2 px-4 pt-4 text-xs text-ink-400 dark:text-ink-500 lg:px-6">
                    <a href="<?php echo e(route('dashboard')); ?>" class="hover:text-ink-600 dark:hover:text-ink-300">Home</a>
                    <?php $__currentLoopData = $breadcrumbs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $crumb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icons['chevron']); ?>"/></svg>
                        <?php if(isset($crumb['url'])): ?>
                            <a href="<?php echo e($crumb['url']); ?>" class="hover:text-ink-600 dark:hover:text-ink-300"><?php echo e($crumb['label']); ?></a>
                        <?php else: ?>
                            <span class="text-ink-600 dark:text-ink-300"><?php echo e($crumb['label']); ?></span>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </nav>
            <?php endif; ?>

            <main class="min-w-0 flex-1 px-4 py-6 lg:px-6">
                <?php if($title): ?>
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">
                        <h1 class="text-xl font-bold text-ink-900 dark:text-ink-50"><?php echo e($title); ?></h1>
                        <div class="flex items-center gap-2">
                            <?php echo e($actions ?? ''); ?>

                        </div>
                    </div>
                <?php endif; ?>

                <?php if(session('success')): ?>
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" x-transition class="mb-4 flex items-center gap-2 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700 dark:border-emerald-800 dark:bg-emerald-500/10 dark:text-emerald-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        <?php echo e(session('success')); ?>

                    </div>
                <?php endif; ?>

                <?php if(session('error')): ?>
                    <div class="mb-4 flex items-center gap-2 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-400">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                        <?php echo e(session('error')); ?>

                    </div>
                <?php endif; ?>

                <?php if($errors->any()): ?>
                    <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-500/10 dark:text-red-400">
                        <ul class="list-disc space-y-0.5 pl-4">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php echo e($slot); ?>

            </main>

            <footer class="border-t border-ink-200 px-6 py-4 text-xs text-ink-400 flex items-center justify-between dark:border-ink-800 dark:text-ink-500">
                <span>Â© <?php echo e(date('Y')); ?> <?php echo e(config('app.name')); ?> â€” Universal CRM + ERP + AI Business Operating System</span>
                <a href="<?php echo e(route('pages.about')); ?>" class="hover:text-ink-600 dark:hover:text-ink-300">About</a>
            </footer>
        </div>
    </div>

    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH C:\MY_ERP\resources\views\components\layouts\app.blade.php ENDPATH**/ ?>