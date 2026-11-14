<?php

namespace App\Services\Rag;

/**
 * Resolves which of the 84 RAG pipelines are most relevant to the page a
 * user is currently looking at, plus the questions that page usually raises.
 *
 * The product guide is rendered on every authenticated page, so this class
 * must stay dependency-free and cheap: pure string matching, no database.
 */
class RagPageGuide
{
    /**
     * Route-name prefix => RAG domains, in priority order.
     *
     * @var array<string, array<int, string>>
     */
    protected const ROUTE_DOMAINS = [
        'customers.' => ['crm'],
        'crm.contacts.' => ['crm'],
        'crm.activities.' => ['crm'],
        'leads.' => ['crm'],
        'opportunities.' => ['crm'],
        'pipeline.' => ['crm'],
        'sales.quotations.' => ['crm', 'finance'],
        'sales.orders.' => ['crm', 'inventory', 'finance'],
        'sales.invoices.' => ['finance', 'crm'],
        'sales.payments.' => ['finance'],
        'procurement.' => ['procurement'],
        'suppliers.' => ['procurement'],
        'supplier-offers.' => ['procurement'],
        'inventory.' => ['inventory'],
        'finance.' => ['finance'],
        'logistics.' => ['logistics'],
        'admin.' => ['executive', 'finance'],
        'ai.' => ['executive'],
        'support.' => ['crm', 'executive'],
        'dashboard' => ['executive', 'crm', 'finance'],
        'getting-started' => ['crm', 'procurement', 'inventory', 'finance', 'logistics', 'executive'],
    ];

    /**
     * Path-prefix fallback for routes that are not named.
     *
     * @var array<string, array<int, string>>
     */
    protected const PATH_DOMAINS = [
        'customers' => ['crm'],
        'contacts' => ['crm'],
        'leads' => ['crm'],
        'opportunities' => ['crm'],
        'pipeline' => ['crm'],
        'sales' => ['crm', 'finance'],
        'procurement' => ['procurement'],
        'suppliers' => ['procurement'],
        'supplier-offers' => ['procurement'],
        'inventory' => ['inventory'],
        'finance' => ['finance'],
        'logistics' => ['logistics'],
        'admin' => ['executive', 'finance'],
        'ai' => ['executive'],
        'support' => ['crm', 'executive'],
    ];

    /**
     * Human labels for the screens people use most.
     *
     * @var array<string, string>
     */
    protected const PAGE_LABELS = [
        'dashboard' => 'Dashboard',
        'getting-started' => 'Getting Started',
        'customers.index' => 'Customers',
        'customers.show' => 'Customer profile',
        'crm.contacts.index' => 'Contacts',
        'leads.index' => 'Leads',
        'opportunities.index' => 'Opportunities',
        'pipeline.index' => 'Sales pipeline',
        'crm.activities.index' => 'Activities',
        'sales.quotations.index' => 'Quotations',
        'sales.orders.index' => 'Sales orders',
        'sales.invoices.index' => 'Invoices',
        'sales.payments.index' => 'Payments',
        'procurement.requisitions.index' => 'Purchase requisitions',
        'procurement.rfqs.index' => 'Requests for quotation',
        'procurement.orders.index' => 'Purchase orders',
        'suppliers.index' => 'Suppliers',
        'supplier-offers.index' => 'Supplier offers',
        'inventory.products.index' => 'Products',
        'inventory.stock.index' => 'Stock movements',
        'inventory.warehouses.index' => 'Warehouses',
        'finance.dashboard' => 'Finance dashboard',
        'finance.accounts.index' => 'Chart of accounts',
        'finance.journals.index' => 'Journal entries',
        'finance.payables' => 'Payables',
        'finance.receivables' => 'Receivables',
        'logistics.shipments.index' => 'Shipments',
        'logistics.containers.index' => 'Containers',
        'logistics.landed-costs.index' => 'Landed costs',
        'ai.rag' => 'RAG workspace',
        'ai.rag.tour' => 'RAG pipeline tour',
        'ai.assistant' => 'AI assistant',
        'ai.copilot' => 'AI copilot',
        'support.index' => 'Help center',
        'support.tickets.index' => 'Support tickets',
        'admin.control-center.index' => 'Control center',
        'admin.audit-logs.index' => 'Audit logs',
        'admin.system-health.index' => 'System health',
    ];

    /**
     * The questions each domain answers best.
     *
     * @var array<string, array<int, string>>
     */
    protected const SUGGESTIONS = [
        'crm' => [
            'Which 10 customers are most likely to churn this quarter?',
            'Which open opportunities are stalled in negotiation?',
            'Which lead source produced the highest revenue this year?',
        ],
        'procurement' => [
            'Which supplier has the worst on-time delivery in the last 90 days?',
            'Where are we paying above benchmark for the same SKU?',
            'What should we replenish next month based on demand and lead time?',
        ],
        'inventory' => [
            'Which SKUs will stock out in the next 14 days?',
            'Which products are dead stock and how much cash is tied up?',
            'Which warehouse is closest to capacity?',
        ],
        'finance' => [
            'Which invoices are 30+ days overdue and who owns the follow-up?',
            'Project our cash position for the next 8 weeks.',
            'Are there duplicate payments to the same supplier?',
        ],
        'logistics' => [
            'Which containers risk demurrage this month?',
            'Which lane has the highest freight cost variance?',
            'Are customs documents complete for next week\'s shipments?',
        ],
        'executive' => [
            'Summarise this month\'s performance for the board.',
            'Which KPIs moved most against target, and why?',
            'What are the top five operational risks right now?',
        ],
    ];

    /**
     * Resolve the relevant domains for a route name and/or request path.
     *
     * @return array<int, string>
     */
    public static function domainsFor(?string $routeName = null, ?string $path = null): array
    {
        $routeName = (string) $routeName;

        if ($routeName !== '') {
            foreach (self::ROUTE_DOMAINS as $prefix => $domains) {
                if ($routeName === $prefix || str_starts_with($routeName, $prefix)) {
                    return $domains;
                }
            }
        }

        $path = trim((string) $path, '/');

        if ($path !== '') {
            $first = explode('/', $path)[0];

            if (isset(self::PATH_DOMAINS[$first])) {
                return self::PATH_DOMAINS[$first];
            }
        }

        return array_keys(RagPipelineRegistry::domainLabels());
    }

    /**
     * A friendly label for the page the user is on.
     */
    public static function pageLabel(?string $routeName = null, ?string $path = null): string
    {
        $routeName = (string) $routeName;

        if ($routeName !== '' && isset(self::PAGE_LABELS[$routeName])) {
            return self::PAGE_LABELS[$routeName];
        }

        $path = trim((string) $path, '/');

        if ($path === '') {
            return 'Dashboard';
        }

        $segment = explode('/', $path)[0];

        return ucfirst(str_replace(['-', '_'], ' ', $segment));
    }

    /**
     * Suggested questions for one domain.
     *
     * @return array<int, string>
     */
    public static function suggestions(string $domain): array
    {
        return self::SUGGESTIONS[$domain] ?? [];
    }

    /**
     * Suggested questions for a list of domains, de-duplicated and capped.
     *
     * @param  array<int, string>  $domains
     * @return array<int, string>
     */
    public static function suggestionsFor(array $domains, int $limit = 6): array
    {
        $questions = [];

        foreach ($domains as $domain) {
            foreach (self::suggestions($domain) as $question) {
                if (! in_array($question, $questions, true)) {
                    $questions[] = $question;
                }
            }
        }

        return array_slice($questions, 0, $limit);
    }

    /**
     * Everything the Blade component needs, in one call.
     *
     * @return array<string, mixed>
     */
    public static function context(?string $routeName = null, ?string $path = null): array
    {
        $domains = self::domainsFor($routeName, $path);
        $labels = RagPipelineRegistry::domainLabels();

        return [
            'page' => self::pageLabel($routeName, $path),
            'route' => (string) $routeName,
            'domains' => $domains,
            'domain_labels' => array_intersect_key($labels, array_flip($domains)),
            'suggestions' => self::suggestionsFor($domains),
        ];
    }
}
