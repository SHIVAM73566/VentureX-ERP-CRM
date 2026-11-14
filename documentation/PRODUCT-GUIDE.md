# VentureX ERP & CRM — Product Guide

The product guide is built into the application. It is not a PDF, a separate
portal or an onboarding call: a **Product guide** launcher sits in the
bottom-right corner of **every authenticated page** and opens a slide-over with
three tabs.

| Tab | What it answers |
| --- | --- |
| **Introduction** | What VentureX is, a 60-second start, how companies use it, the easy process and which features work together. |
| **All 84 RAG pipelines** | The complete, searchable and domain-filtered catalogue of every retrieval-augmented generation pipeline. |
| **This page** | The RAG domains and questions that match the screen the user is currently on. |

Because the guide is rendered by
`resources/views/components/layouts/app.blade.php` through
`<x-product-guide />`, it is available everywhere automatically — including
pages added later — with no per-page wiring and no extra request.

---

## Why the guide exists

Most business software explains itself in a manual that is out of date before
the first invoice is sent. VentureX explains itself **where the work happens**:

- a new user sees the introduction without leaving the dashboard;
- an experienced user can search all 84 pipelines from any screen;
- a warehouse user on the stock page gets stock questions, not finance ones;
- a finance user on receivables gets collection and cash-flow questions;
- every suggestion is grounded in the company's own records.

---

## Start in 60 seconds

1. **Create a customer or supplier** — every document starts from a party.
2. **Add products and opening stock** — products drive quotes, orders, POs and stock.
3. **Run one sales cycle** — quotation → sales order → invoice → payment.
4. **Ask a question in plain English** — open the RAG workspace, choose a pipeline, ask.

The full walkthrough lives at **Getting Started** (`/getting-started`) and in
[GETTING-STARTED.md](GETTING-STARTED.md).

---

## How companies use VentureX

| Company type | Typical outcome |
| --- | --- |
| Distributors & wholesalers | Quote from live stock, reorder before stockouts, track supplier lead time and landed cost per container. |
| Manufacturers | Forecast material requirements, compare RFQ responses, monitor on-time in-full supplier delivery. |
| Professional services | Manage leads, opportunities and pipeline, then connect delivery to invoices and account health. |
| Multi-company groups | Keep each company's data scoped and auditable while executives compare consolidated KPIs. |

---

## The easy process

**Capture → Qualify → Fulfil → Invoice → Analyse**

Every stage writes to the same dataset, so dashboards, the ledger, the audit
trail and the AI always agree.

---

## How the 84 RAG pipelines help everywhere

Retrieval-Augmented Generation (RAG) means the AI answers from **your records**,
not from generic internet knowledge. VentureX ships 84 focused pipelines across
seven domains. A warehouse question searches warehouse data; a finance question
searches the ledger.

How a question is answered:

1. The user picks a pipeline (or the guide suggests one from the current page).
2. The pipeline retrieves the relevant records for that company only.
3. Local 256-dimension embeddings and cosine similarity rank the matches.
4. The answer is composed with **sources, confidence and latency**.
5. The query is logged for audit and quota reporting.

No external vector database is required. Answers still work without an AI
provider configured, because retrieval and ranking are local.

### Page-aware mapping

`App\Services\Rag\RagPageGuide` maps the current route to the domains that
matter on that screen:

| Page | Domains surfaced |
| --- | --- |
| Dashboard | Executive, CRM, Finance |
| Customers, contacts, leads, opportunities, pipeline | CRM |
| Quotations | CRM, Finance |
| Sales orders | CRM, Inventory, Finance |
| Invoices | Finance, CRM |
| Payments | Finance |
| Procurement, suppliers, supplier offers | Procurement |
| Inventory, products, stock, warehouses | Inventory |
| Finance, accounts, journals, payables, receivables | Finance |
| Logistics, shipments, containers, landed costs | Logistics |
| Admin, audit logs, system health | Executive, Finance |
| Help center, tickets | CRM, Executive |
| AI workspace and tour | Executive |
| Getting Started | All seven domains |

Unrecognised screens still receive the full catalogue.

---

## The complete catalogue — all 84 pipelines

### CRM & Sales Analytics (15 pipelines)

- **Customer Lifetime Value** (`customer_lifetime_value`) — Estimates lifetime revenue, margin and retention value for each customer.
- **Churn Risk Scoring** (`churn_risk_scoring`) — Scores churn probability from recency, payment behaviour and engagement signals.
- **Lead Conversion & Sentiment** (`lead_conversion_sentiment`) — Reads lead notes and interactions to estimate conversion intent and sentiment.
- **Pipeline Velocity** (`pipeline_velocity`) — Measures how quickly opportunities move through each pipeline stage.
- **Cross-sell Recommendation** (`cross_sell_recommendation`) — Recommends adjacent products based on purchase and quotation history.
- **Upsell Recommendation** (`upsell_recommendation`) — Identifies customers ready for higher-value products or tiers.
- **Customer 360 Profile** (`customer_360_profile`) — Builds a complete customer view across CRM, sales, finance and support.
- **Sales Forecast Accuracy** (`sales_forecast_accuracy`) — Compares forecasted pipeline value with closed revenue to improve forecasting.
- **Win / Loss Analysis** (`win_loss_analysis`) — Explains why opportunities were won or lost using notes and stage history.
- **Lead Source Attribution** (`lead_source_attribution`) — Attributes revenue and conversions back to lead sources and campaigns.
- **Account Health Score** (`account_health_score`) — Combines activity, payment and support signals into an account health score.
- **Opportunity Stage Risk** (`opportunity_stage_risk`) — Flags opportunities that are stalled or likely to slip between stages.
- **Contact Engagement Recency** (`contact_engagement_recency`) — Ranks contacts by engagement recency and relationship strength.
- **Quote Conversion Probability** (`quote_conversion_probability`) — Predicts the likelihood that a quotation converts to an order.
- **Territory Performance Analysis** (`territory_performance_analysis`) — Compares revenue and pipeline performance across territories and owners.

### Procurement & Supply Chain (15 pipelines)

- **Supplier Reliability Scoring** (`supplier_reliability_scoring`) — Scores suppliers on delivery, quality, pricing and responsiveness.
- **PO Discrepancy Detection** (`po_discrepancy_detection`) — Detects quantity, price and term mismatches between POs and offers.
- **Landed Cost Variance** (`landed_cost_variance`) — Tracks landed-cost variance against estimates and prior shipments.
- **Material Requirement Forecast** (`material_requirement_forecast`) — Forecasts material requirements from requisitions, orders and stock.
- **Dynamic Lead Time Estimation** (`dynamic_lead_time_estimation`) — Estimates supplier lead times from historical delivery performance.
- **Supplier Price Benchmarking** (`supplier_price_benchmarking`) — Benchmarks quoted prices across suppliers and historical purchases.
- **Contract Compliance Monitoring** (`contract_compliance_monitoring`) — Checks purchase activity against agreed supplier terms and contracts.
- **RFQ Response Comparison** (`rfq_response_comparison`) — Compares RFQ responses on price, lead time and completeness.
- **Procurement Spend Analysis** (`procurement_spend_analysis`) — Breaks down spend by supplier, category, product and period.
- **Supplier Risk Concentration** (`supplier_risk_concentration`) — Identifies over-reliance on single suppliers or regions.
- **Purchase Requisition Anomaly** (`purchase_requisition_anomaly`) — Flags unusual requisition quantities, priorities or request patterns.
- **Vendor Onboarding Readiness** (`vendor_onboarding_readiness`) — Checks whether a new vendor has the documents and terms required to trade.
- **Delivery Performance Scoring** (`delivery_performance_scoring`) — Measures on-time, in-full delivery performance by supplier and route.
- **Procurement Savings Opportunity** (`procurement_savings_opportunity`) — Finds savings from price gaps, consolidation and payment terms.
- **Inventory Replenishment Planning** (`inventory_replenishment_planning`) — Plans replenishment from demand, stock cover and supplier lead times.

### Inventory & Warehouse Intelligence (10 pipelines)

- **Stockout Risk Alerting** (`stockout_risk_alerting`) — Flags products at risk of stockout from cover, demand and lead time.
- **ABC Inventory Classification** (`abc_inventory_classification`) — Classifies inventory by value contribution and movement frequency.
- **Dead Stock Reduction** (`dead_stock_reduction`) — Finds slow or non-moving stock and recommends liquidation actions.
- **Reorder Point Tuning** (`reorder_point_tuning`) — Suggests reorder points from historical demand and lead-time variability.
- **Warehouse Utilization Analysis** (`warehouse_utilization_analysis`) — Measures space, movement and throughput utilisation by warehouse.
- **Stock Movement Anomaly** (`stock_movement_anomaly`) — Detects unusual receipts, issues, transfers and adjustments.
- **Cycle Count Optimization** (`cycle_count_optimization`) — Prioritises cycle counts by value, risk and movement frequency.
- **Slow Mover Detection** (`slow_mover_detection`) — Identifies products with declining or negligible turnover.
- **Safety Stock Optimization** (`safety_stock_optimization`) — Recommends safety stock from demand and supply variability.
- **Product Margin & Inventory Mix** (`product_margin_inventory_mix`) — Balances product margin against inventory investment and turnover.

### Finance, Invoicing & Ledger Auditing (15 pipelines)

- **Invoice Anomaly Detection** (`invoice_anomaly_detection`) — Detects unusual invoice amounts, taxes, duplicates and timing.
- **Cash Flow Liquidity Projection** (`cash_flow_liquidity_projection`) — Projects cash position from receivables, payables and payment history.
- **Tax Compliance Check** (`tax_compliance_check`) — Reviews tax rates, invoice tax and filing-relevant patterns.
- **Credit Risk Scoring** (`credit_risk_scoring`) — Scores open-account credit risk from payment behaviour and exposure.
- **Duplicate Payment Detection** (`duplicate_payment_detection`) — Finds repeated or near-duplicate payments to the same party.
- **Receivables Aging Insight** (`receivables_aging_insight`) — Explains aging buckets and prioritises collection actions.
- **Payables Optimization** (`payables_optimization`) — Optimises payment timing, discounts and supplier terms.
- **Journal Entry Integrity** (`journal_entry_integrity`) — Checks journal entries for balance, account and posting anomalies.
- **Revenue Recognition Review** (`revenue_recognition_review`) — Reviews revenue timing against invoice, order and payment data.
- **Expense Pattern Analysis** (`expense_pattern_analysis`) — Finds recurring, rising or unusual expense patterns.
- **Budget Variance Analysis** (`budget_variance_analysis`) — Compares actuals against expected spend and explains variances.
- **Payment Behaviour Scoring** (`payment_behavior_scoring`) — Scores customers and suppliers on payment timeliness and consistency.
- **FX Exposure Analysis** (`fx_exposure_analysis`) — Measures foreign-currency exposure across invoices, POs and payments.
- **Profitability by Customer** (`profitability_by_customer`) — Calculates margin by customer after discounts, costs and credits.
- **Fraud Signal Detection** (`fraud_signal_detection`) — Flags unusual payment, vendor or journal patterns for review.

### Logistics & Customs Clearance (15 pipelines)

- **Container Demurrage Risk** (`container_demurrage_risk`) — Predicts demurrage and detention exposure from container timelines.
- **HS Code Classification** (`hs_code_classification`) — Suggests HS codes from product descriptions and trade history.
- **Freight Rate Benchmarking** (`freight_rate_benchmarking`) — Benchmarks freight rates by lane, mode and carrier.
- **Port Congestion Prediction** (`port_congestion_prediction`) — Estimates port congestion risk from shipment and route history.
- **Customs Duty Optimization** (`customs_duty_optimization`) — Finds duty optimisation opportunities from classification and origin.
- **Shipment Delay Prediction** (`shipment_delay_prediction`) — Predicts delay risk from carrier, route and current milestones.
- **Carrier Performance Scoring** (`carrier_performance_scoring`) — Scores carriers on transit time, cost and exception frequency.
- **Incoterm Risk Analysis** (`incoterm_risk_analysis`) — Reviews Incoterms for cost, risk-transfer and compliance exposure.
- **Route Optimization Insight** (`route_optimization_insight`) — Suggests route and mode improvements from historical shipments.
- **Container Fill Rate Analysis** (`container_fill_rate_analysis`) — Measures container utilisation and consolidation opportunities.
- **Landed Cost Allocation Audit** (`landed_cost_allocation_audit`) — Audits landed-cost allocation across products and shipments.
- **Customs Document Readiness** (`customs_document_readiness`) — Checks whether customs documents are complete before clearance.
- **Warehouse to Port Lead Time** (`warehouse_to_port_leadtime`) — Measures and forecasts warehouse-to-port transit time.
- **Shipment Cost Variance** (`shipment_cost_variance`) — Compares actual shipment costs with quotes and budgets.
- **Trade Compliance Screening** (`trade_compliance_screening`) — Screens parties, countries and goods against trade restrictions.

### Executive Venture Intelligence & Admin (10 pipelines)

- **Board Deck Summarization** (`board_deck_summarization`) — Summarises financial and operational performance for board reporting.
- **Executive KPI Variance Briefing** (`executive_kpi_variance`) — Explains KPI movements and variances across business units.
- **AI Quota Optimization** (`ai_quota_optimization`) — Recommends AI quota allocations from usage, role and demand.
- **Audit Log Behavioral Anomaly** (`audit_log_anomaly_detection`) — Detects unusual user, record and permission activity in audit logs.
- **Company Health Scorecard** (`company_health_scorecard`) — Combines sales, finance, supply and service signals into a scorecard.
- **Cross-module KPI Correlation** (`cross_module_kpi_correlation`) — Finds correlations between CRM, supply, inventory and finance KPIs.
- **Strategic Initiative Tracking** (`strategic_initiative_tracking`) — Tracks initiative progress against milestones and outcomes.
- **Risk Register Summary** (`risk_register_summary`) — Consolidates operational, financial and compliance risks.
- **Operational Efficiency Index** (`operational_efficiency_index`) — Measures throughput, cycle time and exception rates across modules.
- **Governance & Compliance Digest** (`governance_compliance_digest`) — Produces a digest of approvals, audits and compliance exceptions.

### Human Resources & Workforce Intelligence (4 pipelines)

- **Workforce Headcount Forecast** (`workforce_headcount_forecast`) — Forecasts headcount, attrition and hiring demand from the employee directory and leave history.
- **Attendance Pattern Analysis** (`attendance_pattern_analysis`) — Detects absenteeism, lateness and overtime patterns across teams.
- **Payroll Cost Anomaly Detection** (`payroll_cost_anomaly`) — Flags unusual payroll movements, overtime spikes and deduction anomalies before approval.
- **Onboarding Readiness Digest** (`onboarding_readiness_digest`) — Summarises onboarding completion, blocked tasks and new-hire readiness for HR review.

---

## The HR suite

VentureX includes a complete HR workspace at **Human Resources** in the sidebar:

- **HR dashboard** — headcount, attendance today, pending leave, open onboarding
  tasks, latest payroll and recent performance reviews.
- **Employee directory** — searchable records with department, manager, job
  title, employment type, salary and status.
- **Attendance** — daily present/remote/late/absent tracking with worked and
  overtime hours.
- **Leave** — request, approve and reject annual, sick, casual, unpaid and
  statutory leave with automatic day counting.
- **Payroll** — one-click monthly runs with transparent, configurable tax,
  social-security and overtime percentages (`config/hr.php`). Runs are
  regenerated idempotently and must be approved before payment.
- **Performance reviews** — goals and competency scoring with automatic overall
  rating.
- **Onboarding** — a seven-step checklist is generated automatically for every
  new employee and can be completed, reopened or extended.

Every HR record is company-scoped, permission-gated (`hr.view`, `hr.create`,
`hr.edit`, `hr.delete`, `hr.approve`) and audit-logged.

---

## AERO X Agentic Engine

The **Automation → AERO X Agentic Engine** control surface runs autonomous,
auditable business rules:

- Draft purchase orders when products fall to their reorder level.
- Flag sent and partially paid invoices as overdue.
- Self-assign unowned support tickets to the least-loaded agent.
- Generate onboarding checklists for recent hires that have none.

Each rule has a cooldown, confidence score, enable/disable switch and optional
human-approval gate. Every execution runs inside a database transaction; a
failure is rolled back and recorded in the run ledger with the captured
exception, and the rule's confidence is reduced. Runs are scheduled every
15 minutes and can be triggered manually or as a dry run from the UI.

---

## Invoice branding

**Administration → Settings → Branding & Invoices** decouples every visual
detail from the code. Upload a logo, choose an accent colour and PDF-safe
typography, and set the legal name, tax/VAT/GST ID, registration number,
address, footer, terms and watermark. Values fall back to the company record,
so a fresh install still produces a complete tax-compliant invoice. The same
branding is applied to invoice and quotation PDFs and previewed on the invoice
screen.

---

## Using the guide

- **Open it** with the bottom-right **Product guide** button on any page.
- **Search** the catalogue by pipeline name, keyword or question.
- **Filter** to one of the seven domains.
- **Copy** a suggested question from the *This page* tab and paste it into the
  RAG workspace, or follow the domain chip.
- **Close** with `Esc`, the backdrop or the close button.

Keyboard and accessibility: the launcher and close button are real buttons with
`aria-*` attributes, the panel is a labelled `role="dialog"`, and tabs expose
`aria-selected`.

---

## Related documentation

- [Getting Started & Quick Examples](GETTING-STARTED.md)
- [AI API Keys — Secure Setup](AI-API-KEYS.md)
- [84-Pipeline RAG Guide](RAG-GUIDE.md)
- [User Guide](USER_GUIDE.md)
- [Admin Guide](ADMIN_GUIDE.md)
- [API Documentation](API_DOCUMENTATION.md)

In the app:

- `/getting-started` — guided quick start
- `/ai/rag` — RAG workspace
- `/ai/rag/tour` — 84-pipeline tour
- `/support` — Help center
