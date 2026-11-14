# The 84-Pipeline RAG Guide

How VentureX ERP & CRM answers business questions from live company records - and why 84 focused pipelines beat one generic chatbot.

> In-app version: **AI Center → Enterprise RAG → Open the pipeline tour**.

---

## 1. What RAG means in one minute

**Retrieval-Augmented Generation (RAG)** is a way of making AI answer from a specific body of data instead of from general training knowledge.

```
Question
   │
   ▼
Find the most relevant records in your company data   ← retrieval
   │
   ▼
Give those records to the answer step                  ← augmentation
   │
   ▼
Produce an answer with sources and confidence          ← generation
```

In VentureX the retrieval step is fully local: records are converted into short documents, embedded into 256-dimension vectors, and compared with the question using cosine similarity. No external vector database is required. If an AI provider is configured, it only rewrites the retrieved facts into a nicer sentence — the facts still come from your database.

---

## 2. Why 84 pipelines instead of one assistant

| One generic assistant | 84 focused pipelines |
|---|---|
| Has to guess which table matters | Already knows its domain and source models |
| Can mix finance and warehouse context | Searches only relevant records |
| Hard to audit | Every pipeline has a name, scope and sources |
| One prompt for every team | Each team gets a specialist |
| Broad answers | Precise, repeatable answers |

The general **AI Assistant** still exists for open-ended questions. The 84 pipelines are the specialists that make answers precise and explainable.

---

## 3. The seven domains

| Domain | Pipelines | Focus |
|---|---:|---|
| CRM & Sales Analytics | 15 | Customers, churn, pipeline, conversion, territory |
| Procurement & Supply Chain | 15 | Suppliers, POs, RFQs, spend, replenishment |
| Inventory & Warehouse Intelligence | 10 | Stockouts, dead stock, reorder points, utilisation |
| Finance, Invoicing & Ledger Auditing | 15 | Cash, credit, tax, anomalies, profitability |
| Logistics & Customs Clearance | 15 | Demurrage, HS codes, freight, compliance |
| Executive Venture Intelligence & Admin | 10 | Board briefings, KPIs, risk, governance |
| Human Resources & Workforce Intelligence | 4 | Headcount, attendance, payroll anomalies, onboarding readiness |
| **Total** | **84** | |

---

## 4. Full pipeline catalogue

### CRM & Sales Analytics (15)

| Pipeline | What it answers |
|---|---|
| Customer Lifetime Value | Estimates lifetime revenue, margin and retention value per customer. |
| Churn Risk Scoring | Scores churn probability from recency, payment behaviour and engagement. |
| Lead Conversion & Sentiment | Estimates conversion intent and sentiment from lead interactions. |
| Pipeline Velocity | Measures how quickly opportunities move through each stage. |
| Cross-sell Recommendation | Recommends adjacent products from purchase and quotation history. |
| Upsell Recommendation | Identifies customers ready for higher-value products or tiers. |
| Customer 360 Profile | Builds a complete customer view across CRM, sales, finance and support. |
| Sales Forecast Accuracy | Compares forecasted pipeline value with closed revenue. |
| Win / Loss Analysis | Explains why opportunities were won or lost. |
| Lead Source Attribution | Attributes revenue and conversions to lead sources and campaigns. |
| Account Health Score | Combines activity, payment and support signals into a health score. |
| Opportunity Stage Risk | Flags stalled or slipping opportunities. |
| Contact Engagement Recency | Ranks contacts by engagement recency and relationship strength. |
| Quote Conversion Probability | Predicts the likelihood a quotation becomes an order. |
| Territory Performance Analysis | Compares revenue and pipeline performance by territory and owner. |

**Example questions**

- Which 10 customers are most likely to churn this quarter?
- Which open opportunities are stalled in negotiation?
- Which lead source produced the highest revenue this year?

### Procurement & Supply Chain (15)

| Pipeline | What it answers |
|---|---|
| Supplier Reliability Scoring | Scores suppliers on delivery, quality, pricing and responsiveness. |
| PO Discrepancy Detection | Detects quantity, price and term mismatches between POs and offers. |
| Landed Cost Variance | Tracks landed-cost variance against estimates and prior shipments. |
| Material Requirement Forecast | Forecasts material requirements from requisitions, orders and stock. |
| Dynamic Lead Time Estimation | Estimates supplier lead times from historical delivery performance. |
| Supplier Price Benchmarking | Benchmarks quoted prices across suppliers and history. |
| Contract Compliance Monitoring | Checks purchase activity against agreed supplier terms. |
| RFQ Response Comparison | Compares RFQ responses on price, lead time and completeness. |
| Procurement Spend Analysis | Breaks down spend by supplier, category, product and period. |
| Supplier Risk Concentration | Identifies over-reliance on single suppliers or regions. |
| Purchase Requisition Anomaly | Flags unusual requisition quantities, priorities or patterns. |
| Vendor Onboarding Readiness | Checks whether a new vendor has the documents needed to trade. |
| Delivery Performance Scoring | Measures on-time, in-full delivery by supplier and route. |
| Procurement Savings Opportunity | Finds savings from price gaps, consolidation and payment terms. |
| Inventory Replenishment Planning | Plans replenishment from demand, stock cover and lead times. |

**Example questions**

- Which supplier has the worst on-time delivery in the last 90 days?
- Where are we paying above benchmark for the same SKU?
- What should we replenish next month based on demand and lead time?

### Inventory & Warehouse Intelligence (10)

| Pipeline | What it answers |
|---|---|
| Stockout Risk Alerting | Flags products at risk of stockout from cover, demand and lead time. |
| ABC Inventory Classification | Classifies inventory by value contribution and movement frequency. |
| Dead Stock Reduction | Finds slow or non-moving stock and recommends liquidation. |
| Reorder Point Tuning | Suggests reorder points from demand and lead-time variability. |
| Warehouse Utilization Analysis | Measures space, movement and throughput utilisation. |
| Stock Movement Anomaly | Detects unusual receipts, issues, transfers and adjustments. |
| Cycle Count Optimization | Prioritises cycle counts by value, risk and movement. |
| Slow Mover Detection | Identifies products with declining or negligible turnover. |
| Safety Stock Optimization | Recommends safety stock from demand and supply variability. |
| Product Margin & Inventory Mix | Balances product margin against inventory investment and turnover. |

**Example questions**

- Which SKUs will stock out in the next 14 days?
- Which products are dead stock and how much cash is tied up?
- Which warehouse is closest to capacity?

### Finance, Invoicing & Ledger Auditing (15)

| Pipeline | What it answers |
|---|---|
| Invoice Anomaly Detection | Detects unusual invoice amounts, taxes, duplicates and timing. |
| Cash Flow Liquidity Projection | Projects cash position from receivables, payables and history. |
| Tax Compliance Check | Reviews tax rates, invoice tax and filing-relevant patterns. |
| Credit Risk Scoring | Scores open-account credit risk from payment behaviour and exposure. |
| Duplicate Payment Detection | Finds repeated or near-duplicate payments to the same party. |
| Receivables Aging Insight | Explains aging buckets and prioritises collection actions. |
| Payables Optimization | Optimises payment timing, discounts and supplier terms. |
| Journal Entry Integrity | Checks journals for balance, account and posting anomalies. |
| Revenue Recognition Review | Reviews revenue timing against invoice, order and payment data. |
| Expense Pattern Analysis | Finds recurring, rising or unusual expense patterns. |
| Budget Variance Analysis | Compares actuals against expected spend and explains variances. |
| Payment Behaviour Scoring | Scores customers and suppliers on payment timeliness. |
| FX Exposure Analysis | Measures foreign-currency exposure across invoices, POs and payments. |
| Profitability by Customer | Calculates margin by customer after discounts, costs and credits. |
| Fraud Signal Detection | Flags unusual payment, vendor or journal patterns for review. |

**Example questions**

- Which invoices are 30+ days overdue and who owns the follow-up?
- Project our cash position for the next 8 weeks.
- Are there duplicate payments to the same supplier?

### Logistics & Customs Clearance (15)

| Pipeline | What it answers |
|---|---|
| Container Demurrage Risk | Predicts demurrage and detention exposure from container timelines. |
| HS Code Classification | Suggests HS codes from product descriptions and trade history. |
| Freight Rate Benchmarking | Benchmarks freight rates by lane, mode and carrier. |
| Port Congestion Prediction | Estimates port congestion risk from shipment history. |
| Customs Duty Optimization | Finds duty optimisation opportunities from classification and origin. |
| Shipment Delay Prediction | Predicts delay risk from carrier, route and milestones. |
| Carrier Performance Scoring | Scores carriers on transit time, cost and exceptions. |
| Incoterm Risk Analysis | Reviews Incoterms for cost, risk-transfer and compliance exposure. |
| Route Optimization Insight | Suggests route and mode improvements from history. |
| Container Fill Rate Analysis | Measures container utilisation and consolidation opportunities. |
| Landed Cost Allocation Audit | Audits landed-cost allocation across products and shipments. |
| Customs Document Readiness | Checks whether customs documents are complete before clearance. |
| Warehouse to Port Lead Time | Measures and forecasts warehouse-to-port transit time. |
| Shipment Cost Variance | Compares actual shipment costs with quotes and budgets. |
| Trade Compliance Screening | Screens parties, countries and goods against restrictions. |

**Example questions**

- Which containers risk demurrage this month?
- Which lane has the highest freight cost variance?
- Are customs documents complete for next week's shipments?

### Executive Venture Intelligence & Admin (10)

| Pipeline | What it answers |
|---|---|
| Board Deck Summarization | Summarises financial and operational performance for board reporting. |
| Executive KPI Variance Briefing | Explains KPI movements and variances across business units. |
| AI Quota Optimization | Recommends AI quota allocations from usage, role and demand. |
| Audit Log Behavioral Anomaly | Detects unusual user, record and permission activity. |
| Company Health Scorecard | Combines sales, finance, supply and service signals into a scorecard. |
| Cross-module KPI Correlation | Finds correlations between CRM, supply, inventory and finance KPIs. |
| Strategic Initiative Tracking | Tracks initiative progress against milestones and outcomes. |
| Risk Register Summary | Consolidates operational, financial and compliance risks. |
| Operational Efficiency Index | Measures throughput, cycle time and exception rates. |
| Governance & Compliance Digest | Produces a digest of approvals, audits and compliance exceptions. |

**Example questions**

- Summarise this month's performance for the board.
- Which KPIs moved most against target, and why?
- What are the top five operational risks right now?

### Human Resources & Workforce Intelligence (4)

| Pipeline | What it answers |
|---|---|
| Workforce Headcount Forecast | Forecasts headcount, attrition and hiring demand from the employee directory and leave history. |
| Attendance Pattern Analysis | Detects absenteeism, lateness and overtime patterns across teams. |
| Payroll Cost Anomaly Detection | Flags unusual payroll movements, overtime spikes and deduction anomalies before approval. |
| Onboarding Readiness Digest | Summarises onboarding completion, blocked tasks and new-hire readiness for HR review. |

**Example questions**

- Are we likely to be short-staffed next quarter?
- Which team has the worst attendance pattern this month?
- Did any payroll line move unexpectedly compared with last month?
- Which new hires are still blocked in onboarding?

---

## 5. A live enterprise day

| Time | Role | Question | Pipeline | Outcome |
|---|---|---|---|---|
| 08:30 | Owner | What changed since yesterday? | Company Health Scorecard | Decide what needs attention before stand-up |
| 09:15 | Sales | Which quotes are likely to convert? | Quote Conversion Probability | Call the right customers first |
| 11:00 | Procurement | Which supplier is slipping? | Supplier Reliability / Lead Time | Re-plan before production is affected |
| 14:00 | Warehouse | What will stock out? | Stockout Risk Alerting | Raise a transfer or PO today |
| 16:00 | Finance | What cash is coming in? | Cash Flow Liquidity Projection | Prioritise collections and payments |
| 18:00 | Logistics | Any demurrage risk? | Container Demurrage Risk | Act before detention charges start |

---

## 6. Benefits by company type

| Company type | What changes | Outcome |
|---|---|---|
| Small business (5–50) | Owner asks one question instead of opening five screens | No analyst needed; faster decisions |
| Mid-market (50–500) | Every department gets consistent answers from the same data | Fewer ad-hoc reports and less rework |
| Wholesale distributor | Stock, supplier and margin answered together | Less dead stock, fewer stockouts |
| Manufacturer | Material forecasts, lead times and landed cost in one view | Better production planning |
| Importer / exporter | Duty, demurrage, HS codes and compliance screening | Lower landed cost, fewer delays |
| Services agency | Pipeline, conversion and receivables insight | Healthier cash flow |
| Finance & audit | Anomaly detection across invoices, payments and journals | Earlier fraud and error detection |
| Group / enterprise | Company-scoped answers and executive scorecards | Governance without spreadsheet sprawl |

---

## 7. How to use it

### In the UI

1. Open **AI Center → Enterprise RAG**.
2. Click **Index all** (or index a single pipeline) so documents reflect current data.
3. Pick a domain and pipeline.
4. Ask a specific question — include the entity, period and metric.
5. Read the answer, sources, confidence and latency; then act in the module.

### CLI

```bash
php artisan rag:sync --train
```

Indexes every pipeline for the current company and runs the self-training pass.

### REST API

```http
GET  /api/rag/pipelines
GET  /api/rag/stats
POST /api/rag/query
POST /api/rag/sync

Authorization: Bearer <token>
Content-Type: application/json

{
  "pipeline": "receivables_aging_insight",
  "question": "Which invoices are more than 30 days overdue?",
  "limit": 8
}
```

Responses include `pipeline`, `question`, `answer`, `sources[]`, `mode` (`local` or `ai`), `confidence` and `latency_ms`.

---

## 8. Trust and governance

- **Company-scoped.** Documents and query logs carry a company id; users retrieve only their own company's data.
- **Permission-aware.** RAG reuses the AI policy (`ai.view` to query, `ai.create` to index).
- **Local-first.** Embeddings and similarity run on your server; no external vector database.
- **Explainable.** Every answer returns sources, confidence and latency.
- **Auditable.** Indexing and querying are written to audit logs; failures pass through the self-healing interceptor.
- **Self-improving.** The nightly self-training pipeline analyses query and transaction patterns and adjusts pipeline weights and confidence.
- **Human approval.** Answers are decision support. Purchases, payments, exports and permissions still require a human action.

---

## 9. Tuning tips

1. **Index after imports.** New customers, products or POs are not visible to RAG until indexed.
2. **Be specific.** “Overdue invoices for Bluewave Retail in October” beats “show me money”.
3. **Use the right domain.** Finance questions belong in finance, not CRM.
4. **Check confidence.** Low confidence usually means thin data or a vague question.
5. **Review audit logs.** They show what was indexed and what was asked.
6. **Let training run.** Confidence improves as the nightly job learns from real usage.

---

## 10. FAQ

**Does it send my data to an AI provider?**
Only if a provider is configured and the pipeline uses AI mode. Retrieval and local answers work without any provider.

**Do I need Pinecone, Weaviate or another vector database?**
No. Embeddings are stored as JSON and compared in PHP.

**How many documents can it hold?**
The demo dataset indexes 2,709 records across 84 pipelines. The local store scales with your database; index only the pipelines you use for best performance.

**Why did an answer say “local” instead of using the LLM?**
Local mode is the safe fallback when no provider is configured or the provider fails. It still answers from retrieved records.

**Can two companies see each other's answers?**
No. Documents, pipelines and query logs are company-scoped, and the AI policy is enforced on every request.

---

*VentureX ERP & CRM — grounded answers from your own business records.*
