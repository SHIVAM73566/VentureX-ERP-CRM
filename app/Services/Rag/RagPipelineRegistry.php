<?php

namespace App\Services\Rag;

use App\Models\Account;
use App\Models\Activity;
use App\Models\AiUsageLog;
use App\Models\ApprovalRequest;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Container;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Employee;
use App\Models\ErrorReport;
use App\Models\Incoterm;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
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
use App\Models\RfqResponse;
use App\Models\SalesOrder;
use App\Models\Shipment;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\SupplierOffer;
use App\Models\SupportTicket;
use App\Models\TaxRate;
use App\Models\User;
use App\Models\Warehouse;

/**
 * Canonical registry of the 84 enterprise RAG pipelines.
 *
 * 15 CRM & Sales + 15 Procurement + 10 Inventory + 15 Finance
 * + 15 Logistics + 10 Executive/Admin + 4 Human Resources = 84 pipelines.
 */
class RagPipelineRegistry
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function definitions(): array
    {
        $definitions = [];

        foreach (self::crm() as $row) {
            $definitions[] = self::make('crm', $row);
        }
        foreach (self::procurement() as $row) {
            $definitions[] = self::make('procurement', $row);
        }
        foreach (self::inventory() as $row) {
            $definitions[] = self::make('inventory', $row);
        }
        foreach (self::finance() as $row) {
            $definitions[] = self::make('finance', $row);
        }
        foreach (self::logistics() as $row) {
            $definitions[] = self::make('logistics', $row);
        }
        foreach (self::executive() as $row) {
            $definitions[] = self::make('executive', $row);
        }
        foreach (self::hr() as $row) {
            $definitions[] = self::make('hr', $row);
        }

        return $definitions;
    }

    public static function count(): int
    {
        return count(self::definitions());
    }

    /**
     * @return array<string, int>
     */
    public static function domainCounts(): array
    {
        $counts = array_fill_keys(array_keys(self::domainLabels()), 0);
        foreach (self::definitions() as $definition) {
            $counts[$definition['domain']] = ($counts[$definition['domain']] ?? 0) + 1;
        }

        return $counts;
    }

    /**
     * @return array<string, string>
     */
    public static function domainLabels(): array
    {
        return [
            'crm' => 'CRM & Sales Analytics',
            'procurement' => 'Procurement & Supply Chain',
            'inventory' => 'Inventory & Warehouse Intelligence',
            'finance' => 'Finance, Invoicing & Ledger Auditing',
            'logistics' => 'Logistics & Customs Clearance',
            'executive' => 'Executive Venture Intelligence & Admin',
            'hr' => 'Human Resources & Workforce Intelligence',
        ];
    }

    /**
     * @param  array{0: string, 1: string, 2: string, 3: array<int, class-string>, 4: array<int, string>, 5?: string}  $row
     * @return array<string, mixed>
     */
    protected static function make(string $domain, array $row): array
    {
        return [
            'key' => $row[0],
            'name' => $row[1],
            'domain' => $domain,
            'description' => $row[2],
            'source_models' => $row[3],
            'keywords' => $row[4],
            'retrieval_strategy' => $row[5] ?? 'hybrid',
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected static function crm(): array
    {
        return [
            ['customer_lifetime_value', 'Customer Lifetime Value', 'Estimates lifetime revenue, margin and retention value for each customer.', [Customer::class, Invoice::class, Payment::class, SalesOrder::class], ['lifetime', 'value', 'ltv', 'revenue', 'retention']],
            ['churn_risk_scoring', 'Churn Risk Scoring', 'Scores churn probability from recency, payment behaviour and engagement signals.', [Customer::class, Invoice::class, Activity::class, Contact::class], ['churn', 'risk', 'retention', 'at risk']],
            ['lead_conversion_sentiment', 'Lead Conversion & Sentiment', 'Reads lead notes and interactions to estimate conversion intent and sentiment.', [Lead::class, Activity::class, Contact::class], ['lead', 'conversion', 'sentiment', 'intent']],
            ['pipeline_velocity', 'Pipeline Velocity', 'Measures how quickly opportunities move through each pipeline stage.', [Opportunity::class, SalesOrder::class, Quotation::class], ['pipeline', 'velocity', 'stage', 'cycle time']],
            ['cross_sell_recommendation', 'Cross-sell Recommendation', 'Recommends adjacent products based on purchase and quotation history.', [Customer::class, Product::class, SalesOrder::class, Quotation::class], ['cross sell', 'recommendation', 'bundle']],
            ['upsell_recommendation', 'Upsell Recommendation', 'Identifies customers ready for higher-value products or tiers.', [Customer::class, Product::class, SalesOrder::class, Invoice::class], ['upsell', 'upgrade', 'tier']],
            ['customer_360_profile', 'Customer 360 Profile', 'Builds a complete customer view across CRM, sales, finance and support.', [Customer::class, Contact::class, Opportunity::class, Invoice::class, SupportTicket::class], ['customer 360', 'profile', 'overview']],
            ['sales_forecast_accuracy', 'Sales Forecast Accuracy', 'Compares forecasted pipeline value with closed revenue to improve forecasting.', [Opportunity::class, SalesOrder::class, Invoice::class], ['forecast', 'accuracy', 'revenue']],
            ['win_loss_analysis', 'Win / Loss Analysis', 'Explains why opportunities were won or lost using notes and stage history.', [Opportunity::class, Quotation::class, Activity::class], ['win', 'loss', 'reason', 'closed']],
            ['lead_source_attribution', 'Lead Source Attribution', 'Attributes revenue and conversions back to lead sources and campaigns.', [Lead::class, Customer::class, Invoice::class], ['source', 'attribution', 'campaign', 'channel']],
            ['account_health_score', 'Account Health Score', 'Combines activity, payment and support signals into an account health score.', [Customer::class, Invoice::class, Payment::class, SupportTicket::class, Activity::class], ['health', 'score', 'account']],
            ['opportunity_stage_risk', 'Opportunity Stage Risk', 'Flags opportunities that are stalled or likely to slip between stages.', [Opportunity::class, Activity::class, Quotation::class], ['stalled', 'stage', 'risk', 'slip']],
            ['contact_engagement_recency', 'Contact Engagement Recency', 'Ranks contacts by engagement recency and relationship strength.', [Contact::class, Activity::class, Lead::class], ['engagement', 'recency', 'contact']],
            ['quote_conversion_probability', 'Quote Conversion Probability', 'Predicts the likelihood that a quotation converts to an order.', [Quotation::class, SalesOrder::class, Customer::class], ['quote', 'conversion', 'probability']],
            ['territory_performance_analysis', 'Territory Performance Analysis', 'Compares revenue and pipeline performance across territories and owners.', [Customer::class, Opportunity::class, Invoice::class, User::class], ['territory', 'performance', 'region']],
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected static function procurement(): array
    {
        return [
            ['supplier_reliability_scoring', 'Supplier Reliability Scoring', 'Scores suppliers on delivery, quality, pricing and responsiveness.', [Supplier::class, PurchaseOrder::class, SupplierOffer::class], ['supplier', 'reliability', 'score']],
            ['po_discrepancy_detection', 'PO Discrepancy Detection', 'Detects quantity, price and term mismatches between POs and offers.', [PurchaseOrder::class, SupplierOffer::class, Supplier::class], ['discrepancy', 'purchase order', 'variance']],
            ['landed_cost_variance', 'Landed Cost Variance', 'Tracks landed-cost variance against estimates and prior shipments.', [LandedCost::class, PurchaseOrder::class, Shipment::class], ['landed cost', 'variance', 'freight']],
            ['material_requirement_forecast', 'Material Requirement Forecast', 'Forecasts material requirements from requisitions, orders and stock.', [PurchaseRequisition::class, PurchaseOrder::class, Product::class, StockMovement::class], ['material', 'requirement', 'forecast']],
            ['dynamic_lead_time_estimation', 'Dynamic Lead Time Estimation', 'Estimates supplier lead times from historical delivery performance.', [PurchaseOrder::class, Supplier::class, Shipment::class], ['lead time', 'delivery', 'estimation']],
            ['supplier_price_benchmarking', 'Supplier Price Benchmarking', 'Benchmarks quoted prices across suppliers and historical purchases.', [SupplierOffer::class, PurchaseOrder::class, Supplier::class, Product::class], ['benchmark', 'price', 'supplier']],
            ['contract_compliance_monitoring', 'Contract Compliance Monitoring', 'Checks purchase activity against agreed supplier terms and contracts.', [Supplier::class, PurchaseOrder::class, SupplierOffer::class], ['contract', 'compliance', 'terms']],
            ['rfq_response_comparison', 'RFQ Response Comparison', 'Compares RFQ responses on price, lead time and completeness.', [Rfq::class, RfqResponse::class, Supplier::class], ['rfq', 'response', 'comparison']],
            ['procurement_spend_analysis', 'Procurement Spend Analysis', 'Breaks down spend by supplier, category, product and period.', [PurchaseOrder::class, Supplier::class, Product::class], ['spend', 'category', 'procurement']],
            ['supplier_risk_concentration', 'Supplier Risk Concentration', 'Identifies over-reliance on single suppliers or regions.', [Supplier::class, PurchaseOrder::class, Country::class], ['concentration', 'risk', 'supplier']],
            ['purchase_requisition_anomaly', 'Purchase Requisition Anomaly', 'Flags unusual requisition quantities, priorities or request patterns.', [PurchaseRequisition::class, PurchaseOrder::class, User::class], ['requisition', 'anomaly', 'unusual']],
            ['vendor_onboarding_readiness', 'Vendor Onboarding Readiness', 'Checks whether a new vendor has the documents and terms required to trade.', [Supplier::class, Document::class, Country::class], ['vendor', 'onboarding', 'readiness']],
            ['delivery_performance_scoring', 'Delivery Performance Scoring', 'Measures on-time, in-full delivery performance by supplier and route.', [PurchaseOrder::class, Shipment::class, Supplier::class], ['delivery', 'otif', 'performance']],
            ['procurement_savings_opportunity', 'Procurement Savings Opportunity', 'Finds savings from price gaps, consolidation and payment terms.', [SupplierOffer::class, PurchaseOrder::class, Supplier::class], ['savings', 'opportunity', 'cost']],
            ['inventory_replenishment_planning', 'Inventory Replenishment Planning', 'Plans replenishment from demand, stock cover and supplier lead times.', [Product::class, StockMovement::class, PurchaseOrder::class, Warehouse::class], ['replenishment', 'planning', 'stock']],
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected static function inventory(): array
    {
        return [
            ['stockout_risk_alerting', 'Stockout Risk Alerting', 'Flags products at risk of stockout from cover, demand and lead time.', [Product::class, StockMovement::class, Warehouse::class], ['stockout', 'risk', 'alert']],
            ['abc_inventory_classification', 'ABC Inventory Classification', 'Classifies inventory by value contribution and movement frequency.', [Product::class, StockMovement::class, Invoice::class], ['abc', 'classification', 'inventory']],
            ['dead_stock_reduction', 'Dead Stock Reduction', 'Finds slow or non-moving stock and recommends liquidation actions.', [Product::class, StockMovement::class, Warehouse::class], ['dead stock', 'slow moving', 'liquidation']],
            ['reorder_point_tuning', 'Reorder Point Tuning', 'Suggests reorder points from historical demand and lead-time variability.', [Product::class, StockMovement::class, PurchaseOrder::class], ['reorder', 'point', 'tuning']],
            ['warehouse_utilization_analysis', 'Warehouse Utilization Analysis', 'Measures space, movement and throughput utilisation by warehouse.', [Warehouse::class, StockMovement::class, Product::class], ['warehouse', 'utilization', 'capacity']],
            ['stock_movement_anomaly', 'Stock Movement Anomaly', 'Detects unusual receipts, issues, transfers and adjustments.', [StockMovement::class, Product::class, Warehouse::class, User::class], ['movement', 'anomaly', 'adjustment']],
            ['cycle_count_optimization', 'Cycle Count Optimization', 'Prioritises cycle counts by value, risk and movement frequency.', [Product::class, StockMovement::class, Warehouse::class], ['cycle count', 'audit', 'stocktake']],
            ['slow_mover_detection', 'Slow Mover Detection', 'Identifies products with declining or negligible turnover.', [Product::class, StockMovement::class, SalesOrder::class], ['slow mover', 'turnover', 'decline']],
            ['safety_stock_optimization', 'Safety Stock Optimization', 'Recommends safety stock from demand and supply variability.', [Product::class, StockMovement::class, PurchaseOrder::class], ['safety stock', 'buffer', 'variability']],
            ['product_margin_inventory_mix', 'Product Margin & Inventory Mix', 'Balances product margin against inventory investment and turnover.', [Product::class, Invoice::class, StockMovement::class], ['margin', 'inventory mix', 'turnover']],
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected static function finance(): array
    {
        return [
            ['invoice_anomaly_detection', 'Invoice Anomaly Detection', 'Detects unusual invoice amounts, taxes, duplicates and timing.', [Invoice::class, Payment::class, Customer::class], ['invoice', 'anomaly', 'duplicate']],
            ['cash_flow_liquidity_projection', 'Cash Flow Liquidity Projection', 'Projects cash position from receivables, payables and payment history.', [Invoice::class, Payment::class, PurchaseOrder::class], ['cash flow', 'liquidity', 'projection']],
            ['tax_compliance_check', 'Tax Compliance Check', 'Reviews tax rates, invoice tax and filing-relevant patterns.', [Invoice::class, TaxRate::class, Customer::class], ['tax', 'compliance', 'gst', 'vat']],
            ['credit_risk_scoring', 'Credit Risk Scoring', 'Scores open-account credit risk from payment behaviour and exposure.', [Customer::class, Invoice::class, Payment::class], ['credit', 'risk', 'exposure']],
            ['duplicate_payment_detection', 'Duplicate Payment Detection', 'Finds repeated or near-duplicate payments to the same party.', [Payment::class, Invoice::class, Supplier::class], ['duplicate', 'payment', 'double']],
            ['receivables_aging_insight', 'Receivables Aging Insight', 'Explains aging buckets and prioritises collection actions.', [Invoice::class, Customer::class, Payment::class], ['receivables', 'aging', 'overdue']],
            ['payables_optimization', 'Payables Optimization', 'Optimises payment timing, discounts and supplier terms.', [PurchaseOrder::class, Supplier::class, Payment::class], ['payables', 'discount', 'terms']],
            ['journal_entry_integrity', 'Journal Entry Integrity', 'Checks journal entries for balance, account and posting anomalies.', [JournalEntry::class, JournalEntryLine::class, Account::class], ['journal', 'integrity', 'ledger']],
            ['revenue_recognition_review', 'Revenue Recognition Review', 'Reviews revenue timing against invoice, order and payment data.', [Invoice::class, SalesOrder::class, JournalEntry::class], ['revenue', 'recognition', 'accrual']],
            ['expense_pattern_analysis', 'Expense Pattern Analysis', 'Finds recurring, rising or unusual expense patterns.', [JournalEntryLine::class, Account::class, PurchaseOrder::class], ['expense', 'pattern', 'cost']],
            ['budget_variance_analysis', 'Budget Variance Analysis', 'Compares actuals against expected spend and explains variances.', [JournalEntryLine::class, Account::class, PurchaseOrder::class], ['budget', 'variance', 'actual']],
            ['payment_behavior_scoring', 'Payment Behaviour Scoring', 'Scores customers and suppliers on payment timeliness and consistency.', [Payment::class, Invoice::class, Customer::class, Supplier::class], ['payment', 'behaviour', 'timeliness']],
            ['fx_exposure_analysis', 'FX Exposure Analysis', 'Measures foreign-currency exposure across invoices, POs and payments.', [Invoice::class, PurchaseOrder::class, Currency::class], ['fx', 'currency', 'exposure']],
            ['profitability_by_customer', 'Profitability by Customer', 'Calculates margin by customer after discounts, costs and credits.', [Customer::class, Invoice::class, Payment::class, Product::class], ['profitability', 'margin', 'customer']],
            ['fraud_signal_detection', 'Fraud Signal Detection', 'Flags unusual payment, vendor or journal patterns for review.', [Payment::class, Supplier::class, JournalEntry::class, AuditLog::class], ['fraud', 'signal', 'suspicious']],
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected static function logistics(): array
    {
        return [
            ['container_demurrage_risk', 'Container Demurrage Risk', 'Predicts demurrage and detention exposure from container timelines.', [Container::class, Shipment::class, LandedCost::class], ['demurrage', 'detention', 'container']],
            ['hs_code_classification', 'HS Code Classification', 'Suggests HS codes from product descriptions and trade history.', [Product::class, Shipment::class, Country::class], ['hs code', 'classification', 'tariff']],
            ['freight_rate_benchmarking', 'Freight Rate Benchmarking', 'Benchmarks freight rates by lane, mode and carrier.', [Shipment::class, LandedCost::class, Container::class], ['freight', 'rate', 'benchmark']],
            ['port_congestion_prediction', 'Port Congestion Prediction', 'Estimates port congestion risk from shipment and route history.', [Shipment::class, Country::class, Container::class], ['port', 'congestion', 'delay']],
            ['customs_duty_optimization', 'Customs Duty Optimization', 'Finds duty optimisation opportunities from classification and origin.', [LandedCost::class, Shipment::class, Country::class, Incoterm::class], ['customs', 'duty', 'optimization']],
            ['shipment_delay_prediction', 'Shipment Delay Prediction', 'Predicts delay risk from carrier, route and current milestones.', [Shipment::class, Container::class, Supplier::class], ['delay', 'shipment', 'eta']],
            ['carrier_performance_scoring', 'Carrier Performance Scoring', 'Scores carriers on transit time, cost and exception frequency.', [Shipment::class, Container::class, LandedCost::class], ['carrier', 'performance', 'transit']],
            ['incoterm_risk_analysis', 'Incoterm Risk Analysis', 'Reviews Incoterms for cost, risk-transfer and compliance exposure.', [Incoterm::class, Shipment::class, PurchaseOrder::class], ['incoterm', 'risk', 'terms']],
            ['route_optimization_insight', 'Route Optimization Insight', 'Suggests route and mode improvements from historical shipments.', [Shipment::class, Country::class, Container::class], ['route', 'optimization', 'lane']],
            ['container_fill_rate_analysis', 'Container Fill Rate Analysis', 'Measures container utilisation and consolidation opportunities.', [Container::class, Shipment::class, Product::class], ['fill rate', 'container', 'consolidation']],
            ['landed_cost_allocation_audit', 'Landed Cost Allocation Audit', 'Audits landed-cost allocation across products and shipments.', [LandedCost::class, Shipment::class, PurchaseOrder::class, Product::class], ['landed cost', 'allocation', 'audit']],
            ['customs_document_readiness', 'Customs Document Readiness', 'Checks whether customs documents are complete before clearance.', [Shipment::class, Document::class, Country::class], ['customs', 'document', 'clearance']],
            ['warehouse_to_port_leadtime', 'Warehouse to Port Lead Time', 'Measures and forecasts warehouse-to-port transit time.', [Shipment::class, Warehouse::class, Container::class], ['lead time', 'port', 'warehouse']],
            ['shipment_cost_variance', 'Shipment Cost Variance', 'Compares actual shipment costs with quotes and budgets.', [Shipment::class, LandedCost::class, PurchaseOrder::class], ['shipment', 'cost', 'variance']],
            ['trade_compliance_screening', 'Trade Compliance Screening', 'Screens parties, countries and goods against trade restrictions.', [Shipment::class, Supplier::class, Country::class, Document::class], ['trade', 'compliance', 'screening']],
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected static function executive(): array
    {
        return [
            ['board_deck_summarization', 'Board Deck Summarization', 'Summarises financial and operational performance for board reporting.', [Company::class, Invoice::class, PurchaseOrder::class, Opportunity::class], ['board', 'summary', 'deck']],
            ['executive_kpi_variance', 'Executive KPI Variance Briefing', 'Explains KPI movements and variances across business units.', [Company::class, Invoice::class, SalesOrder::class, PurchaseOrder::class], ['kpi', 'variance', 'briefing']],
            ['ai_quota_optimization', 'AI Quota Optimization', 'Recommends AI quota allocations from usage, role and demand.', [AiUsageLog::class, User::class, Company::class], ['ai quota', 'usage', 'allocation']],
            ['audit_log_anomaly_detection', 'Audit Log Behavioral Anomaly', 'Detects unusual user, record and permission activity in audit logs.', [AuditLog::class, User::class], ['audit', 'anomaly', 'behaviour']],
            ['company_health_scorecard', 'Company Health Scorecard', 'Combines sales, finance, supply and service signals into a scorecard.', [Company::class, Invoice::class, Payment::class, PurchaseOrder::class, SupportTicket::class], ['health', 'scorecard', 'company']],
            ['cross_module_kpi_correlation', 'Cross-module KPI Correlation', 'Finds correlations between CRM, supply, inventory and finance KPIs.', [Invoice::class, PurchaseOrder::class, StockMovement::class, Opportunity::class], ['correlation', 'kpi', 'cross module']],
            ['strategic_initiative_tracking', 'Strategic Initiative Tracking', 'Tracks initiative progress against milestones and outcomes.', [ApprovalRequest::class, Document::class, Company::class], ['initiative', 'strategy', 'milestone']],
            ['risk_register_summary', 'Risk Register Summary', 'Consolidates operational, financial and compliance risks.', [ErrorReport::class, AuditLog::class, Supplier::class, Invoice::class], ['risk', 'register', 'summary']],
            ['operational_efficiency_index', 'Operational Efficiency Index', 'Measures throughput, cycle time and exception rates across modules.', [Invoice::class, PurchaseOrder::class, Shipment::class, StockMovement::class], ['efficiency', 'throughput', 'cycle']],
            ['governance_compliance_digest', 'Governance & Compliance Digest', 'Produces a digest of approvals, audits and compliance exceptions.', [ApprovalRequest::class, AuditLog::class, Document::class, SupportTicket::class], ['governance', 'compliance', 'digest']],
        ];
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    protected static function hr(): array
    {
        return [
            ['workforce_headcount_forecast', 'Workforce Headcount Forecast', 'Forecasts headcount, attrition and hiring demand from the employee directory and leave history.', [Employee::class, Department::class, LeaveRequest::class], ['headcount', 'attrition', 'forecast']],
            ['attendance_pattern_analysis', 'Attendance Pattern Analysis', 'Detects absenteeism, lateness and overtime patterns across teams.', [Attendance::class, Employee::class, Department::class], ['attendance', 'absence', 'overtime']],
            ['payroll_cost_anomaly', 'Payroll Cost Anomaly Detection', 'Flags unusual payroll movements, overtime spikes and deduction anomalies before approval.', [PayrollRun::class, Employee::class, Attendance::class], ['payroll', 'cost', 'anomaly']],
            ['onboarding_readiness_digest', 'Onboarding Readiness Digest', 'Summarises onboarding completion, blocked tasks and new-hire readiness for HR review.', [OnboardingTask::class, Employee::class, PerformanceReview::class], ['onboarding', 'readiness', 'new hire']],
        ];
    }
}
