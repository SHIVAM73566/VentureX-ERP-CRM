<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Activity;
use App\Models\AgentAction;
use App\Models\AgentRun;
use App\Models\AiRun;
use App\Models\AiSkill;
use App\Models\ApprovalRequest;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Container;
use App\Models\Customer;
use App\Models\Department;
use App\Models\Document;
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
use App\Policies\AccountPolicy;
use App\Policies\ActivityPolicy;
use App\Policies\AgentActionPolicy;
use App\Policies\AgentRunPolicy;
use App\Policies\AiRunPolicy;
use App\Policies\AiSkillPolicy;
use App\Policies\ApprovalRequestPolicy;
use App\Policies\AttendancePolicy;
use App\Policies\AuditLogPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\ContactPolicy;
use App\Policies\ContainerPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\ErrorReportPolicy;
use App\Policies\ExportRequestPolicy;
use App\Policies\InvoicePolicy;
use App\Policies\JournalEntryPolicy;
use App\Policies\LandedCostPolicy;
use App\Policies\LeadPolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\OnboardingTaskPolicy;
use App\Policies\OpportunityPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PayrollRunPolicy;
use App\Policies\PerformanceReviewPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\PurchaseRequisitionPolicy;
use App\Policies\QuotationPolicy;
use App\Policies\RfqPolicy;
use App\Policies\RolePolicy;
use App\Policies\SalesOrderPolicy;
use App\Policies\ShipmentPolicy;
use App\Policies\StockMovementPolicy;
use App\Policies\SupplierOfferPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\SupportTicketPolicy;
use App\Policies\UserPolicy;
use App\Policies\WarehousePolicy;
use App\Services\SettingService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SettingService::class);
    }

    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });

        Gate::define('configure', fn (User $user) => $user->hasPermissionTo('settings.configure'));

        $this->registerPolicies();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Lead::class, LeadPolicy::class);
        Gate::policy(Opportunity::class, OpportunityPolicy::class);
        Gate::policy(Supplier::class, SupplierPolicy::class);
        Gate::policy(SupplierOffer::class, SupplierOfferPolicy::class);
        Gate::policy(Document::class, DocumentPolicy::class);
        Gate::policy(Quotation::class, QuotationPolicy::class);
        Gate::policy(SalesOrder::class, SalesOrderPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Warehouse::class, WarehousePolicy::class);
        Gate::policy(StockMovement::class, StockMovementPolicy::class);
        Gate::policy(PurchaseRequisition::class, PurchaseRequisitionPolicy::class);
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
        Gate::policy(Rfq::class, RfqPolicy::class);
        Gate::policy(Shipment::class, ShipmentPolicy::class);
        Gate::policy(Container::class, ContainerPolicy::class);
        Gate::policy(LandedCost::class, LandedCostPolicy::class);
        Gate::policy(Account::class, AccountPolicy::class);
        Gate::policy(JournalEntry::class, JournalEntryPolicy::class);
        Gate::policy(Contact::class, ContactPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
        Gate::policy(AiSkill::class, AiSkillPolicy::class);
        Gate::policy(AiRun::class, AiRunPolicy::class);
        Gate::policy(ExportRequest::class, ExportRequestPolicy::class);
        Gate::policy(ApprovalRequest::class, ApprovalRequestPolicy::class);
        Gate::policy(SupportTicket::class, SupportTicketPolicy::class);
        Gate::policy(ErrorReport::class, ErrorReportPolicy::class);

        // HR suite
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(PayrollRun::class, PayrollRunPolicy::class);
        Gate::policy(PerformanceReview::class, PerformanceReviewPolicy::class);
        Gate::policy(OnboardingTask::class, OnboardingTaskPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);

        // AERO X Agentic Engine
        Gate::policy(AgentAction::class, AgentActionPolicy::class);
        Gate::policy(AgentRun::class, AgentRunPolicy::class);
    }
}
