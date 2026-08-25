<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\JournalEntryController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\OpportunityController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\SalesOrderController;
use App\Http\Controllers\Api\StockMovementController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\SupportTicketController;
use App\Http\Controllers\Api\TokenController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| VentureX ERP & CRM REST API
| All routes are prefixed with /api
| Authentication: Bearer token via Laravel Sanctum
|
*/

// Protected API routes
Route::middleware(['auth:sanctum', 'throttle:api'])->name('api.')->group(function () {

    // ─── Token Management ──────────────────────────────────────────────
    Route::get('/tokens', [TokenController::class, 'index'])->name('tokens.index');
    Route::post('/tokens', [TokenController::class, 'store'])->name('tokens.create');
    Route::delete('/tokens/{id}', [TokenController::class, 'destroy'])->name('tokens.destroy');

    // ─── CRM ───────────────────────────────────────────────────────────
    Route::prefix('crm')->group(function () {
        Route::apiResource('customers', CustomerController::class);
        Route::apiResource('contacts', ContactController::class)->only(['index', 'store', 'show', 'update', 'destroy']);
        Route::apiResource('leads', LeadController::class);
        Route::apiResource('opportunities', OpportunityController::class);
    });

    // ─── Sales ─────────────────────────────────────────────────────────
    Route::prefix('sales')->group(function () {
        Route::apiResource('quotations', QuotationController::class);
        Route::apiResource('orders', SalesOrderController::class)->parameters(['orders' => 'salesOrder']);
        Route::apiResource('invoices', InvoiceController::class);
        Route::apiResource('payments', PaymentController::class);
    });

    // ─── Inventory ─────────────────────────────────────────────────────
    Route::prefix('inventory')->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::apiResource('warehouses', WarehouseController::class);
        Route::apiResource('stock-movements', StockMovementController::class)->only(['index', 'store', 'show', 'destroy']);
    });

    // ─── Procurement ───────────────────────────────────────────────────
    Route::prefix('procurement')->group(function () {
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
    });

    // ─── Finance ───────────────────────────────────────────────────────
    Route::prefix('finance')->group(function () {
        Route::apiResource('accounts', AccountController::class);
        Route::apiResource('journal-entries', JournalEntryController::class);
    });

    // ─── AI ────────────────────────────────────────────────────────────
    Route::prefix('ai')->group(function () {
        Route::get('/conversations', [AiController::class, 'conversations'])->name('conversations');
        Route::post('/conversations', [AiController::class, 'storeConversation'])->name('conversations.store');
        Route::get('/conversations/{conversation}', [AiController::class, 'showConversation'])->name('conversations.show');
        Route::post('/conversations/{conversation}/messages', [AiController::class, 'sendMessage'])->name('conversations.messages');
        Route::get('/insights', [AiController::class, 'insights'])->name('insights');
    });

    // ─── Support ───────────────────────────────────────────────────────
    Route::prefix('support')->group(function () {
        Route::apiResource('tickets', SupportTicketController::class);
    });

    // ─── User Profile ──────────────────────────────────────────────────
    Route::get('/user', fn () => response()->json([
        'success' => true,
        'data' => auth()->user(),
    ]))->name('user');
});
