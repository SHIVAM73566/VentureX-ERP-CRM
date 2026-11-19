<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Activity;
use App\Models\AgentAction;
use App\Models\AiRun;
use App\Models\Attendance;
use App\Models\Contact;
use App\Models\Container;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Employee;
use App\Models\ErrorReport;
use App\Models\ExportRequest;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\LandedCost;
use App\Models\Lead;
use App\Models\LeaveRequest;
use App\Models\OnboardingTask;
use App\Models\Opportunity;
use App\Models\Payment;
use App\Models\PayrollRun;
use App\Models\PerformanceReview;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\SalesOrder;
use App\Models\Shipment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Models\SupportTicket;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

/**
 * Central source of truth for deciding whether a link should be rendered.
 *
 * The map mirrors the authorization checks performed by the target
 * controllers. Missing permission rows (fresh or partial installs) are
 * treated as "no access" so the layout never breaks while rendering a link.
 */
class NavigationAccess
{
    /** @var array<string, array{0: string, 1: class-string|null}> */
    private const ROUTES = [
        'ai.assistant' => ['viewAny', AiRun::class],
        'ai.copilot' => ['viewAny', AiRun::class],
        'ai.document-reader' => ['viewAny', AiRun::class],
        'ai.procurement' => ['viewAny', AiRun::class],
        'ai.rag' => ['viewAny', AiRun::class],
        'ai.rag.tour' => ['viewAny', AiRun::class],
        'ai.support-assistant' => ['viewAny', AiRun::class],
        'ai.usage' => ['viewAny', AiRun::class],

        'crm.activities.index' => ['viewAny', Activity::class],
        'crm.contacts.index' => ['viewAny', Contact::class],
        'customers.create' => ['create', Customer::class],
        'customers.index' => ['viewAny', Customer::class],

        'finance.accounts.index' => ['viewAny', Account::class],
        'finance.dashboard' => ['viewAny', Account::class],
        'finance.journals.index' => ['viewAny', JournalEntry::class],
        'finance.payables' => ['viewAny', PurchaseOrder::class],
        'finance.receivables' => ['viewAny', Invoice::class],

        'hr.attendance.index' => ['viewAny', Attendance::class],
        'hr.dashboard' => ['viewAny', Employee::class],
        'hr.departments.index' => ['viewAny', Department::class],
        'hr.employees.index' => ['viewAny', Employee::class],
        'hr.leave.index' => ['viewAny', LeaveRequest::class],
        'hr.onboarding.index' => ['viewAny', OnboardingTask::class],
        'hr.payroll.index' => ['viewAny', PayrollRun::class],
        'hr.reviews.index' => ['viewAny', PerformanceReview::class],

        'inventory.products.create' => ['create', Product::class],
        'inventory.products.index' => ['viewAny', Product::class],
        'inventory.stock.index' => ['viewAny', StockMovement::class],
        'inventory.warehouses.index' => ['viewAny', Warehouse::class],

        'leads.index' => ['viewAny', Lead::class],
        'logistics.containers.index' => ['viewAny', Container::class],
        'logistics.landed-costs.index' => ['viewAny', LandedCost::class],
        'logistics.shipments.index' => ['viewAny', Shipment::class],

        'opportunities.index' => ['viewAny', Opportunity::class],
        'pipeline.index' => ['viewAny', Opportunity::class],

        'procurement.orders.index' => ['viewAny', PurchaseOrder::class],
        'procurement.requisitions.index' => ['viewAny', PurchaseRequisition::class],
        'procurement.rfqs.index' => ['viewAny', Rfq::class],

        'sales.invoices.index' => ['viewAny', Invoice::class],
        'sales.orders.index' => ['viewAny', SalesOrder::class],
        'sales.payments.index' => ['viewAny', Payment::class],
        'sales.quotations.index' => ['viewAny', Quotation::class],
        'supplier-offers.index' => ['viewAny', SupplierOffer::class],
        'suppliers.index' => ['viewAny', Supplier::class],

        'support.report-error' => ['create', ErrorReport::class],
        'support.tickets.create' => ['create', SupportTicket::class],
        'support.tickets.index' => ['viewAny', SupportTicket::class],

        'admin.agentic.index' => ['viewAny', AgentAction::class],
        'admin.documents.index' => ['configure', null],
        'admin.exports.index' => ['viewAny', ExportRequest::class],
        'admin.imports.index' => ['exports.import', null],
        'admin.users.index' => ['viewAny', User::class],
    ];

    public function can(string $routeName, ?Authenticatable $user = null): bool
    {
        $user ??= Auth::user();

        if (! $user) {
            return false;
        }

        if (! array_key_exists($routeName, self::ROUTES)) {
            return true;
        }

        [$ability, $target] = self::ROUTES[$routeName];

        try {
            return $target
                ? $user->can($ability, $target)
                : $user->can($ability);
        } catch (\Throwable) {
            return false;
        }
    }
}
