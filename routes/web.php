<?php

use App\Http\Controllers\Admin\AiQuotaController;
use App\Http\Controllers\Admin\AiSkillController;
use App\Http\Controllers\Admin\ApprovalController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\SystemHealthController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SecurityController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Ai\AiActionController;
use App\Http\Controllers\Ai\AiAssistantController;
use App\Http\Controllers\Ai\AiDocumentReaderController;
use App\Http\Controllers\Ai\AiInsightsController;
use App\Http\Controllers\Ai\AiUsageController;
use App\Http\Controllers\Ai\CopilotController;
use App\Http\Controllers\Ai\SupportAssistantController;
use App\Http\Controllers\Ai\DeepAnalysisController;
use App\Http\Controllers\Ai\ExecutiveController;
use App\Http\Controllers\Ai\ProcurementAiController;
use App\Http\Controllers\Ai\UsagePlanController;
use App\Http\Controllers\Auth\DataDeletionController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\MfaController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\SecurityVerificationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\StepUpController;
use App\Http\Controllers\Crm\ActivityController;
use App\Http\Controllers\Crm\ContactController;
use App\Http\Controllers\Crm\CustomerController;
use App\Http\Controllers\Crm\LeadController;
use App\Http\Controllers\Crm\OpportunityController;
use App\Http\Controllers\Crm\PipelineController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Finance\AccountController;
use App\Http\Controllers\Finance\FinanceDashboardController;
use App\Http\Controllers\Finance\JournalEntryController;
use App\Http\Controllers\Finance\PayablesController;
use App\Http\Controllers\Finance\ReceivablesController;
use App\Http\Controllers\Inventory\ProductController;
use App\Http\Controllers\Inventory\StockMovementController;
use App\Http\Controllers\Inventory\WarehouseController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Logistics\ContainerController;
use App\Http\Controllers\Logistics\LandedCostController;
use App\Http\Controllers\Logistics\ShipmentController;
use App\Http\Controllers\PagesController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\Procurement\PurchaseOrderController;
use App\Http\Controllers\Procurement\PurchaseRequisitionController;
use App\Http\Controllers\Procurement\RfqController;
use App\Http\Controllers\Procurement\SupplierController;
use App\Http\Controllers\Procurement\SupplierOfferController;
use App\Http\Controllers\Sales\InvoiceController;
use App\Http\Controllers\Sales\PaymentController;
use App\Http\Controllers\Sales\QuotationController;
use App\Http\Controllers\Sales\SalesOrderController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Support\AdminControlCenter;
use App\Http\Controllers\Support\AdminErrorCenter;
use App\Http\Controllers\Support\AdminSupportController;
use App\Http\Controllers\Support\ErrorReportController;
use App\Http\Controllers\Support\HelpCenterController;
use App\Http\Controllers\Support\SupportReplyController;
use App\Http\Controllers\Support\SupportTicketController;
use App\Services\LicenseManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index']);
Route::get('/about', [PagesController::class, 'about'])->name('pages.about');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login')->name('login.attempt');
    Route::get('/register', [SecurityVerificationController::class, 'showRegister'])->name('register');
    Route::post('/auth/register/step1', [SecurityVerificationController::class, 'registerStep1'])->middleware('throttle:5,1')->name('register.step1');
    Route::post('/auth/register/step2', [SecurityVerificationController::class, 'registerStep2'])->middleware('throttle:5,1')->name('register.step2');
    Route::post('/auth/register/step3', [SecurityVerificationController::class, 'registerStep3'])->middleware('throttle:5,1')->name('register.step3');

    // Password Reset
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'reset'])->middleware('throttle:3,1')->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('mfa/setup', [MfaController::class, 'setup'])->name('mfa.setup');
    Route::post('mfa/setup', [MfaController::class, 'enable'])->middleware('throttle:mfa')->name('mfa.enable');
    Route::get('mfa/recovery', [MfaController::class, 'recovery'])->name('mfa.recovery');
    Route::get('mfa/challenge', [MfaController::class, 'challenge'])->name('mfa.challenge');
    Route::post('mfa/challenge', [MfaController::class, 'verify'])->middleware('throttle:mfa')->name('mfa.verify');
    Route::post('mfa/disable', [MfaController::class, 'disable'])->middleware(['step_up', 'throttle:mfa'])->name('mfa.disable');

    Route::get('step-up', [StepUpController::class, 'show'])->name('step-up.show');
    Route::post('step-up', [StepUpController::class, 'verify'])->middleware('throttle:5,1')->name('step-up.verify');

    Route::get('account/sessions', [SessionController::class, 'index'])->name('sessions.index');

    // Security Verification (Phone, Selfie, App Lock, Device Trust)
    Route::get('auth/selfie', [SecurityVerificationController::class, 'showSelfieUpload'])->name('selfie.upload');
    Route::post('auth/selfie/upload', [SecurityVerificationController::class, 'uploadSelfie'])->middleware('throttle:upload')->name('selfie.upload.store');
    Route::get('auth/unlock', [SecurityVerificationController::class, 'showLockScreen'])->name('lock.screen');
    Route::post('auth/unlock', [SecurityVerificationController::class, 'unlock'])->middleware('throttle:5,1')->name('lock.unlock');
    Route::post('auth/lock/set', [SecurityVerificationController::class, 'setLock'])->name('lock.set');
    Route::get('auth/devices', [SecurityVerificationController::class, 'trustedDevices'])->name('devices.index');
    Route::post('auth/devices/{device}/trust', [SecurityVerificationController::class, 'trustDevice'])->name('devices.trust');
    Route::delete('auth/devices/{device}', [SecurityVerificationController::class, 'removeDevice'])->name('devices.remove');

    // GDPR / CCPA Data Privacy Routes
    Route::get('privacy/delete', [DataDeletionController::class, 'showForm'])->name('privacy.delete-form');
    Route::post('privacy/export', [DataDeletionController::class, 'exportData'])->middleware('throttle:3,1')->name('privacy.export');
    Route::delete('privacy/delete', [DataDeletionController::class, 'deleteAccount'])->name('privacy.delete');
    Route::delete('account/sessions/{id}', [SessionController::class, 'destroy'])->name('sessions.destroy');
    Route::delete('account/sessions', [SessionController::class, 'revokeAll'])->name('sessions.revoke-all');
});

Route::middleware(['auth', 'two_factor'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('throttle:search')->name('dashboard');
    Route::get('/search', [SearchController::class, 'index'])->middleware('throttle:search')->name('search');

    Route::middleware(['role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('companies', CompanyController::class)->except('show');
        Route::resource('users', UserController::class)->except('show');
        Route::resource('roles', RoleController::class)->except('show');
        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->middleware('step_up')->name('settings.update');
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [DocumentController::class, 'store'])->middleware('throttle:upload')->name('documents.store');
        Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::get('exports', [ExportController::class, 'index'])->name('exports.index');
        Route::post('exports', [ExportController::class, 'store'])->middleware('throttle:export')->name('exports.store');
        Route::post('exports/{export}/approve', [ExportController::class, 'approve'])->name('exports.approve');
        Route::post('exports/{export}/reject', [ExportController::class, 'reject'])->name('exports.reject');
        Route::get('exports/{export}/download', [ExportController::class, 'download'])->name('exports.download');
        Route::delete('exports/{export}', [ExportController::class, 'destroy'])->name('exports.destroy');
    });

    Route::resource('customers', CustomerController::class);
    Route::resource('leads', LeadController::class);
    Route::resource('opportunities', OpportunityController::class);

    Route::resource('suppliers', SupplierController::class);
    Route::get('supplier-offers', [SupplierOfferController::class, 'index'])->name('supplier-offers.index');
    Route::get('supplier-offers/create', [SupplierOfferController::class, 'create'])->name('supplier-offers.create');
    Route::post('supplier-offers', [SupplierOfferController::class, 'store'])->name('supplier-offers.store');
    Route::get('supplier-offers/{offer}', [SupplierOfferController::class, 'show'])->name('supplier-offers.show');
    Route::get('supplier-offers/{offer}/edit', [SupplierOfferController::class, 'edit'])->name('supplier-offers.edit');
    Route::put('supplier-offers/{offer}', [SupplierOfferController::class, 'update'])->name('supplier-offers.update');
    Route::delete('supplier-offers/{offer}', [SupplierOfferController::class, 'destroy'])->name('supplier-offers.destroy');

    Route::get('pipeline', [PipelineController::class, 'index'])->name('pipeline.index');
    Route::patch('pipeline/{opportunity}', [PipelineController::class, 'move'])->name('pipeline.move');

    Route::get('contacts', [ContactController::class, 'index'])->name('crm.contacts.index');
    Route::get('contacts/create', [ContactController::class, 'create'])->name('crm.contacts.create');
    Route::post('contacts', [ContactController::class, 'store'])->name('crm.contacts.store');
    Route::get('contacts/{contact}/edit', [ContactController::class, 'edit'])->name('crm.contacts.edit');
    Route::put('contacts/{contact}', [ContactController::class, 'update'])->name('crm.contacts.update');
    Route::delete('contacts/{contact}', [ContactController::class, 'destroy'])->name('crm.contacts.destroy');

    Route::get('activities', [ActivityController::class, 'index'])->name('crm.activities.index');
    Route::get('activities/create', [ActivityController::class, 'create'])->name('crm.activities.create');
    Route::post('activities', [ActivityController::class, 'store'])->name('crm.activities.store');
    Route::post('activities/{activity}/complete', [ActivityController::class, 'complete'])->name('crm.activities.complete');
    Route::post('activities/{activity}/reopen', [ActivityController::class, 'reopen'])->name('crm.activities.reopen');
    Route::delete('activities/{activity}', [ActivityController::class, 'destroy'])->name('crm.activities.destroy');

    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('quotations', [QuotationController::class, 'index'])->name('quotations.index');
        Route::get('quotations/create', [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('quotations', [QuotationController::class, 'store'])->name('quotations.store');
        Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');
        Route::get('quotations/{quotation}/edit', [QuotationController::class, 'edit'])->name('quotations.edit');
        Route::put('quotations/{quotation}', [QuotationController::class, 'update'])->name('quotations.update');
        Route::delete('quotations/{quotation}', [QuotationController::class, 'destroy'])->name('quotations.destroy');
        Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convertToOrder'])->name('quotations.convert');
        Route::get('quotations/{quotation}/pdf', [QuotationController::class, 'pdf'])->name('quotations.pdf');

        Route::get('orders', [SalesOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/create', [SalesOrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [SalesOrderController::class, 'store'])->name('orders.store');
        Route::get('orders/{order}', [SalesOrderController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/edit', [SalesOrderController::class, 'edit'])->name('orders.edit');
        Route::put('orders/{order}', [SalesOrderController::class, 'update'])->name('orders.update');
        Route::delete('orders/{order}', [SalesOrderController::class, 'destroy'])->name('orders.destroy');

        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
        Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    Route::prefix('inventory')->name('inventory.')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
        Route::post('warehouses', [WarehouseController::class, 'store'])->name('warehouses.store');
        Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
        Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->name('warehouses.update');
        Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->name('warehouses.destroy');

        Route::get('stock', [StockMovementController::class, 'index'])->name('stock.index');
        Route::get('stock/create', [StockMovementController::class, 'create'])->name('stock.create');
        Route::post('stock', [StockMovementController::class, 'store'])->name('stock.store');
    });

    Route::prefix('procurement')->name('procurement.')->group(function () {
        Route::get('requisitions', [PurchaseRequisitionController::class, 'index'])->name('requisitions.index');
        Route::get('requisitions/create', [PurchaseRequisitionController::class, 'create'])->name('requisitions.create');
        Route::post('requisitions', [PurchaseRequisitionController::class, 'store'])->name('requisitions.store');
        Route::get('requisitions/{requisition}', [PurchaseRequisitionController::class, 'show'])->name('requisitions.show');
        Route::get('requisitions/{requisition}/edit', [PurchaseRequisitionController::class, 'edit'])->name('requisitions.edit');
        Route::put('requisitions/{requisition}', [PurchaseRequisitionController::class, 'update'])->name('requisitions.update');
        Route::delete('requisitions/{requisition}', [PurchaseRequisitionController::class, 'destroy'])->name('requisitions.destroy');

        Route::get('orders', [PurchaseOrderController::class, 'index'])->name('orders.index');
        Route::get('orders/create', [PurchaseOrderController::class, 'create'])->name('orders.create');
        Route::post('orders', [PurchaseOrderController::class, 'store'])->name('orders.store');
        Route::get('orders/{order}', [PurchaseOrderController::class, 'show'])->name('orders.show');
        Route::get('orders/{order}/edit', [PurchaseOrderController::class, 'edit'])->name('orders.edit');
        Route::put('orders/{order}', [PurchaseOrderController::class, 'update'])->name('orders.update');
        Route::delete('orders/{order}', [PurchaseOrderController::class, 'destroy'])->name('orders.destroy');

        Route::get('rfqs', [RfqController::class, 'index'])->name('rfqs.index');
        Route::get('rfqs/create', [RfqController::class, 'create'])->name('rfqs.create');
        Route::post('rfqs', [RfqController::class, 'store'])->name('rfqs.store');
        Route::get('rfqs/{rfq}', [RfqController::class, 'show'])->name('rfqs.show');
        Route::get('rfqs/{rfq}/edit', [RfqController::class, 'edit'])->name('rfqs.edit');
        Route::put('rfqs/{rfq}', [RfqController::class, 'update'])->name('rfqs.update');
        Route::delete('rfqs/{rfq}', [RfqController::class, 'destroy'])->name('rfqs.destroy');
        Route::post('rfqs/{rfq}/create-order', [RfqController::class, 'createOrder'])->name('rfqs.create-order');
    });

    Route::prefix('logistics')->name('logistics.')->group(function () {
        Route::get('shipments', [ShipmentController::class, 'index'])->name('shipments.index');
        Route::get('shipments/create', [ShipmentController::class, 'create'])->name('shipments.create');
        Route::post('shipments', [ShipmentController::class, 'store'])->name('shipments.store');
        Route::get('shipments/{shipment}', [ShipmentController::class, 'show'])->name('shipments.show');
        Route::get('shipments/{shipment}/edit', [ShipmentController::class, 'edit'])->name('shipments.edit');
        Route::put('shipments/{shipment}', [ShipmentController::class, 'update'])->name('shipments.update');
        Route::delete('shipments/{shipment}', [ShipmentController::class, 'destroy'])->name('shipments.destroy');

        Route::get('containers', [ContainerController::class, 'index'])->name('containers.index');
        Route::get('containers/create', [ContainerController::class, 'create'])->name('containers.create');
        Route::post('containers', [ContainerController::class, 'store'])->name('containers.store');
        Route::get('containers/{container}', [ContainerController::class, 'show'])->name('containers.show');
        Route::get('containers/{container}/edit', [ContainerController::class, 'edit'])->name('containers.edit');
        Route::put('containers/{container}', [ContainerController::class, 'update'])->name('containers.update');
        Route::delete('containers/{container}', [ContainerController::class, 'destroy'])->name('containers.destroy');

        Route::get('landed-costs', [LandedCostController::class, 'index'])->name('landed-costs.index');
        Route::get('landed-costs/create', [LandedCostController::class, 'create'])->name('landed-costs.create');
        Route::post('landed-costs', [LandedCostController::class, 'store'])->name('landed-costs.store');
        Route::get('landed-costs/{cost}/edit', [LandedCostController::class, 'edit'])->name('landed-costs.edit');
        Route::put('landed-costs/{cost}', [LandedCostController::class, 'update'])->name('landed-costs.update');
        Route::delete('landed-costs/{cost}', [LandedCostController::class, 'destroy'])->name('landed-costs.destroy');
    });

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('dashboard', [FinanceDashboardController::class, 'index'])->name('dashboard');
        Route::get('receivables', [ReceivablesController::class, 'index'])->name('receivables');
        Route::get('payables', [PayablesController::class, 'index'])->name('payables');

        Route::get('accounts', [AccountController::class, 'index'])->name('accounts.index');
        Route::get('accounts/create', [AccountController::class, 'create'])->name('accounts.create');
        Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');
        Route::get('accounts/{account}', [AccountController::class, 'show'])->name('accounts.show');
        Route::get('accounts/{account}/edit', [AccountController::class, 'edit'])->name('accounts.edit');
        Route::put('accounts/{account}', [AccountController::class, 'update'])->name('accounts.update');
        Route::delete('accounts/{account}', [AccountController::class, 'destroy'])->name('accounts.destroy');

        Route::get('journals', [JournalEntryController::class, 'index'])->name('journals.index');
        Route::get('journals/create', [JournalEntryController::class, 'create'])->name('journals.create');
        Route::post('journals', [JournalEntryController::class, 'store'])->name('journals.store');
        Route::get('journals/{entry}', [JournalEntryController::class, 'show'])->name('journals.show');
        Route::post('journals/{entry}/post', [JournalEntryController::class, 'post'])->name('journals.post');
        Route::delete('journals/{entry}', [JournalEntryController::class, 'destroy'])->name('journals.destroy');
    });

    Route::middleware(['role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('audit-logs/{log}', [AuditLogController::class, 'show'])->name('audit-logs.show');
        Route::get('ai-skills', [AiSkillController::class, 'index'])->name('ai-skills.index');
        Route::get('ai-skills/{skill}/edit', [AiSkillController::class, 'edit'])->name('ai-skills.edit');
        Route::put('ai-skills/{skill}', [AiSkillController::class, 'update'])->name('ai-skills.update');

        Route::get('approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('approvals/{approval}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('approvals/{approval}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('approvals/{approval}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('approvals/{approval}/cancel', [ApprovalController::class, 'cancel'])->name('approvals.cancel');

        Route::get('ai-quotas', [AiQuotaController::class, 'index'])->name('ai-quotas.index');
        Route::put('ai-quotas', [AiQuotaController::class, 'update'])->name('ai-quotas.update');

        Route::get('system-health', [SystemHealthController::class, 'index'])->name('system-health.index');
    });

    Route::get('ai/document-reader', [AiDocumentReaderController::class, 'index'])->name('ai.document-reader');
    Route::post('ai/document-reader', [AiDocumentReaderController::class, 'analyze'])->middleware('throttle:ai')->name('ai.document-reader.analyze');
    Route::get('ai/procurement', [ProcurementAiController::class, 'index'])->name('ai.procurement');
    Route::post('ai/procurement', [ProcurementAiController::class, 'analyze'])->middleware('throttle:ai')->name('ai.procurement.analyze');

    Route::get('ai/assistant', [AiAssistantController::class, 'index'])->middleware('throttle:search')->name('ai.assistant');
    Route::post('ai/assistant', [AiAssistantController::class, 'send'])->middleware('throttle:ai')->name('ai.assistant.send');

    Route::get('ai/copilot', [CopilotController::class, 'index'])->name('ai.copilot');
    Route::post('ai/copilot', [CopilotController::class, 'ask'])->middleware('throttle:ai')->name('ai.copilot.ask');

    Route::get('ai/usage', [AiUsageController::class, 'index'])->name('ai.usage');
    Route::get('ai/quota', [AiUsageController::class, 'quota'])->name('ai.quota');
    Route::get('ai/plan', [UsagePlanController::class, 'index'])->name('ai.usage-plan.index');
    Route::get('ai/plan/status', [UsagePlanController::class, 'apiStatus'])->name('ai.usage-plan.status');

    Route::post('ai/insights/generate', [AiInsightsController::class, 'generate'])->middleware('throttle:ai')->name('ai.insights.generate');

    Route::prefix('ai/actions')->middleware('throttle:ai')->group(function () {
        Route::post('customer-summary', [AiActionController::class, 'customerSummary'])->name('ai.actions.customer-summary');
        Route::post('lead-email', [AiActionController::class, 'leadEmail'])->name('ai.actions.lead-email');
        Route::post('supplier-analysis', [AiActionController::class, 'supplierAnalysis'])->name('ai.actions.supplier-analysis');
        Route::post('invoice-summary', [AiActionController::class, 'invoiceSummary'])->name('ai.actions.invoice-summary');
        Route::post('inventory', [AiActionController::class, 'inventory'])->name('ai.actions.inventory');
        Route::post('finance', [AiActionController::class, 'finance'])->name('ai.actions.finance');

        // Specialist high-value analyses (route to Claude via the gateway).
        Route::post('deep/supplier', [DeepAnalysisController::class, 'supplier'])->name('ai.deep.supplier');
        Route::post('deep/negotiation', [DeepAnalysisController::class, 'negotiation'])->name('ai.deep.negotiation');
        Route::post('deep/customer', [DeepAnalysisController::class, 'customer'])->name('ai.deep.customer');
        Route::post('deep/opportunity', [DeepAnalysisController::class, 'opportunity'])->name('ai.deep.opportunity');
        Route::post('deep/procurement', [DeepAnalysisController::class, 'procurement'])->name('ai.deep.procurement');
    });

    Route::get('ai/support-assistant', [SupportAssistantController::class, 'index'])->name('ai.support-assistant');
    Route::post('ai/support-assistant/ask', [SupportAssistantController::class, 'ask'])->middleware('throttle:ai')->name('ai.support-assistant.ask');

    Route::post('ai/executive-review', [ExecutiveController::class, 'review'])->middleware('throttle:ai')->name('ai.executive.review');
    Route::post('ai/daily-priorities', [ExecutiveController::class, 'daily'])->middleware('throttle:ai')->name('ai.executive.daily');
});

// Security Dashboard Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('admin/security')->name('security.')->group(function () {
    Route::get('/', [SecurityController::class, 'index'])->name('dashboard');
    Route::get('/events', [SecurityController::class, 'events'])->name('events');
    Route::post('/lockdown', [SecurityController::class, 'lockdown'])->middleware('step_up')->name('lockdown');
    Route::post('/block-ip', [SecurityController::class, 'blockIp'])->name('block-ip');
    Route::get('/license', function () {
        $license = LicenseManager::getLicenseInfo();

        return view('admin.security.license', compact('license'));
    })->name('license');
    Route::post('/license', function (Request $request) {
        $key = $request->input('license_key', '');
        if (strlen($key) < 10) {
            return back()->with('error', 'Please enter a valid license key.');
        }
        $result = LicenseManager::validateKey($key);
        if ($result['valid']) {
            config(['app.license_key' => $key]);

            return back()->with('success', 'License key accepted. Tier: '.($result['tier'] ?? 'standard'));
        }

        return back()->with('error', 'License validation failed: '.($result['error'] ?? 'Unknown error'));
    })->middleware('throttle:5,1')->name('license.activate');
});

// ============================================================
// Customer Support System
// ============================================================

Route::middleware(['auth'])->prefix('support')->name('support.')->group(function () {
    Route::get('/', [HelpCenterController::class, 'index'])->name('index');
    Route::get('docs', [HelpCenterController::class, 'docs'])->name('docs');
    Route::get('installation', [HelpCenterController::class, 'installation'])->name('installation');
    Route::get('faq', [HelpCenterController::class, 'faq'])->name('faq');
    Route::get('contact', [HelpCenterController::class, 'contact'])->name('contact');
    Route::post('contact', [HelpCenterController::class, 'submitContact'])->middleware('throttle:10,1')->name('contact.submit');
    Route::get('report-error', [HelpCenterController::class, 'submitError'])->name('report-error');
    Route::post('report-error', [HelpCenterController::class, 'storeError'])->middleware('throttle:10,1')->name('report-error.store');

    Route::get('notifications', [HelpCenterController::class, 'notifications'])->name('notifications');
    Route::patch('notifications/{notification}/mark-read', [HelpCenterController::class, 'markNotificationRead'])->name('notifications.mark-read');
    Route::patch('notifications/{notification}/mark-unread', [HelpCenterController::class, 'markNotificationUnread'])->name('notifications.mark-unread');

    Route::get('tickets', [SupportTicketController::class, 'index'])->name('tickets.index');
    Route::get('tickets/create', [SupportTicketController::class, 'create'])->name('tickets.create');
    Route::post('tickets', [SupportTicketController::class, 'store'])->middleware('throttle:10,1')->name('tickets.store');
    Route::get('tickets/{ticket}', [SupportTicketController::class, 'show'])->name('tickets.show');
    Route::put('tickets/{ticket}', [SupportTicketController::class, 'update'])->name('tickets.update');

    Route::post('tickets/{ticket}/reply', [SupportReplyController::class, 'store'])->middleware('throttle:20,1')->name('tickets.reply');
    Route::post('errors/report', [ErrorReportController::class, 'store'])->name('errors.report');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin/support')->name('admin.support.')->group(function () {
    Route::get('/', [AdminSupportController::class, 'index'])->name('index');
    Route::get('tickets/{ticket}', [AdminSupportController::class, 'show'])->name('tickets.show');
    Route::post('tickets/{ticket}/assign', [AdminSupportController::class, 'assign'])->name('tickets.assign');
    Route::put('tickets/{ticket}/status', [AdminSupportController::class, 'updateStatus'])->name('tickets.status');
    Route::post('tickets/{ticket}/note', [AdminSupportController::class, 'addNote'])->name('tickets.note');
    Route::post('tickets/{ticket}/replies', [SupportReplyController::class, 'store'])->name('replies.store');
    Route::put('tickets/{ticket}', [AdminSupportController::class, 'update'])->name('update');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin/errors')->name('admin.errors.')->group(function () {
    Route::get('/', [AdminErrorCenter::class, 'index'])->name('index');
    Route::get('{error}', [AdminErrorCenter::class, 'show'])->name('show');
    Route::put('{error}', [AdminErrorCenter::class, 'update'])->name('update');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin/control-center')->name('admin.control-center.')->group(function () {
    Route::get('/', [AdminControlCenter::class, 'index'])->name('index');
    Route::get('customers', [AdminControlCenter::class, 'customers'])->name('customers');
    Route::get('customers/{company}', [AdminControlCenter::class, 'customer'])->name('customer');
    Route::get('announcements', [AdminControlCenter::class, 'announcements'])->name('announcements');
    Route::post('announcements', [AdminControlCenter::class, 'storeAnnouncement'])->name('announcements.store');
    Route::delete('announcements/{announcement}', [AdminControlCenter::class, 'destroyAnnouncement'])->name('announcements.destroy');
    Route::get('updates', [AdminControlCenter::class, 'updates'])->name('updates');
    Route::post('updates', [AdminControlCenter::class, 'storeUpdate'])->name('updates.store');
    Route::delete('updates/{update}', [AdminControlCenter::class, 'destroyUpdate'])->name('updates.destroy');
});

// Data Import Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('admin/imports')->name('admin.imports.')->group(function () {
    Route::get('/', [ImportController::class, 'index'])->name('index');
    Route::get('/create', [ImportController::class, 'create'])->name('create');
    Route::post('/upload', [ImportController::class, 'upload'])->name('upload');
    Route::post('/mapping/save', [ImportController::class, 'saveMapping'])->name('mapping.save');
    Route::post('/execute', [ImportController::class, 'execute'])->name('execute');
    Route::get('/history', [ImportController::class, 'history'])->name('history');
    Route::get('/{import}', [ImportController::class, 'show'])->name('show');
    Route::delete('/{import}', [ImportController::class, 'destroy'])->name('destroy');
    Route::get('/{import}/error-report', [ImportController::class, 'errorReport'])->name('error-report');
    Route::post('/templates', [ImportController::class, 'templateStore'])->name('templates.store');
    Route::delete('/templates/{template}', [ImportController::class, 'templateDestroy'])->name('templates.destroy');
});

// Pricing & License Purchase Routes (Payoneer)
Route::get('pricing', [\App\Http\Controllers\PricingController::class, 'index'])->name('pricing');
Route::post('pricing/checkout', [\App\Http\Controllers\PricingController::class, 'checkout'])->name('pricing.checkout');
Route::get('pricing/success', [\App\Http\Controllers\PricingController::class, 'success'])->name('pricing.success');
Route::get('pricing/cancel', [\App\Http\Controllers\PricingController::class, 'cancel'])->name('pricing.cancel');
Route::post('pricing/webhook', [\App\Http\Controllers\PricingController::class, 'webhook'])->middleware('throttle:60,1')->name('pricing.webhook');

// PayPal Payment Routes
Route::middleware(['auth'])->prefix('payment')->name('payment.')->group(function () {
    Route::get('/', [PayPalController::class, 'index'])->name('index');
    Route::get('/send', [PayPalController::class, 'sendPayment'])->name('send');
    Route::post('/create-order', [PayPalController::class, 'createOrder'])->middleware('throttle:10,1')->name('create-order');
    Route::post('/create-custom-order', [PayPalController::class, 'createCustomOrder'])->middleware('throttle:10,1')->name('create-custom-order');
    Route::post('/capture', [PayPalController::class, 'captureOrder'])->middleware('throttle:10,1')->name('capture');
    Route::get('/success', [PayPalController::class, 'success'])->name('success');
    Route::get('/cancel', [PayPalController::class, 'cancel'])->name('cancel');
    Route::get('/history', [PayPalController::class, 'history'])->name('history');
});

// PayPal webhook — no auth middleware (PayPal servers call this)
Route::post('/paypal/webhook', [PayPalController::class, 'webhook'])->middleware('throttle:60,1')->name('paypal.webhook');
