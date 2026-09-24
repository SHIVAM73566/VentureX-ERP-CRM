<?php

/*
|--------------------------------------------------------------------------
| VentureX ERP & CRM — AI Gateway Configuration
|--------------------------------------------------------------------------
| All provider calls are made by the backend AI Gateway only. The browser
| never talks to a provider directly. API keys are injected via environment
| variables (RAPIDAPI_KEY / provider keys) and are never exposed to clients.
*/

return [

    'provider' => env('AI_PROVIDER', 'nvidia'),

    'model' => env('AI_MODEL', 'nvidia/llama-3.3-nemotron-super-49b-v1.5'),

    'temperature' => (float) env('AI_TEMPERATURE', 0.4),

    'top_p' => (float) env('AI_TOP_P', 0.9),

    'max_tokens' => (int) env('AI_MAX_TOKENS', 2048),

    /*
    | The gateway routes every request to exactly ONE provider. The default
    | provider is used when the requested task has no explicit mapping or the
    | mapped provider is not configured.
    */
    'default_provider' => env('AI_DEFAULT_PROVIDER', 'nvidia'),

    /*
    | Optional: when set (AI_DOCS_PASSWORD), the AI configuration and
    | documentation pages are protected behind an HTTP Basic prompt using
    | this password. Leave empty to keep the pages open to signed-in admins.
    */
    'docs_password' => env('AI_DOCS_PASSWORD'),

    /*
    | How long AI-derived static insights stay cached (seconds).
    */
    'cache_ttl' => (int) (env('AI_CACHE_TTL_HOURS', 24) * 3600),

    /*
    | Outbound request timeout and per-call retries (same provider only).
    | Cross-provider fallback is handled by the gateway fallback_order.
    */
    'request_timeout' => (int) env('AI_REQUEST_TIMEOUT', 60),

    'retries' => (int) env('AI_RETRIES', 1),

    'dedup_lock_ttl' => (int) env('AI_DEDUP_LOCK_TTL', 180),

    /*
    | Providers tried (in order) when the primary provider fails or is not
    | configured. Only one provider is called per request.
    */
    'fallback_order' => array_values(array_filter(array_map(
        fn ($p) => trim($p),
        explode(',', (string) env('AI_FALLBACK_ORDER', 'gemini,nvidia,openai'))
    ))),

    'rate_limit' => [
        'max_per_user_per_hour' => (int) env('AI_RATE_MAX_USER_PER_HOUR', 60),
        'max_per_user_per_day' => (int) env('AI_RATE_MAX_USER_PER_DAY', 200),
        'max_per_user_per_month' => (int) env('AI_RATE_MAX_USER_PER_MONTH', 2000),
        'max_per_company_per_hour' => (int) env('AI_RATE_MAX_COMPANY_PER_HOUR', 300),
    ],

    /*
    | AI Quota System — daily and weekly limits per user, role, and company.
    | When a quota is exhausted the gateway blocks further requests and the
    | UI offers a "Request More AI Access" button for admin approval.
    */
    'quota' => [
        'enabled' => (bool) env('AI_QUOTA_ENABLED', true),

        // Default daily/weekly limits per role (override via admin UI later)
        'defaults' => [
            'user' => ['daily' => 1,   'weekly' => 3],
            'worker' => ['daily' => 10,  'weekly' => 30],
            'employee' => ['daily' => 10,  'weekly' => 30],
            'viewer' => ['daily' => 5,   'weekly' => 15],
            'staff' => ['daily' => 20,  'weekly' => 80],
            'procurement_officer' => ['daily' => 30, 'weekly' => 120],
            'accountant' => ['daily' => 30,  'weekly' => 120],
            'sales_executive' => ['daily' => 30,  'weekly' => 120],
            'sales_manager' => ['daily' => 50,  'weekly' => 200],
            'purchase_manager' => ['daily' => 50,  'weekly' => 200],
            'warehouse_manager' => ['daily' => 30,  'weekly' => 120],
            'logistics_manager' => ['daily' => 30,  'weekly' => 120],
            'finance_manager' => ['daily' => 50,  'weekly' => 200],
            'hr_manager' => ['daily' => 30,  'weekly' => 120],
            'company_admin' => ['daily' => 100, 'weekly' => 500],
            'ceo' => ['daily' => 100, 'weekly' => 500],
            'cfo' => ['daily' => 100, 'weekly' => 500],
            'coo' => ['daily' => 100, 'weekly' => 500],
            'super_admin' => ['daily' => 999, 'weekly' => 9999],
        ],

        // Alert thresholds (percentage of quota used)
        'alert_thresholds' => [50, 75, 90, 100],
    ],

    /*
    | Provider selection per task. Each value is an ordered list of providers
    | to try (first configured+healthy wins). The gateway respects this list
    | before falling back to the global fallback_order.
    |
    | Complex analysis  -> Gemini first (strong reasoning)
    | Code/technical    -> NVIDIA first (fast, code-capable)
    | Creative          -> Gemini/OpenAI
    | General purpose   -> NVIDIA first (fast default)
    */
    'task_routing' => [
        // Complex analysis — Gemini excels at long-context reasoning
        'deep_analysis' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'executive_summary' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'executive_review' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'deep_supplier_analysis' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'deep_customer_analysis' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'complex_document_analysis' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'complex_business_question' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'strategic_recommendation' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'negotiation_strategy' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'opportunity_analysis' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'supplier_comparison' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'daily_priorities' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],

        // Code/technical — NVIDIA and OpenAI are strongest for code
        'code_review' => ['providers' => ['nvidia', 'openai'], 'fallback' => 'local'],
        'technical_analysis' => ['providers' => ['nvidia', 'openai'], 'fallback' => 'local'],

        // Creative/analysis — Gemini and OpenAI for creative writing
        'creative_writing' => ['providers' => ['gemini', 'openai'], 'fallback' => 'local'],
        'market_analysis' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],

        // Quick tasks — fast/cheap providers first
        'quick_question' => ['providers' => ['nvidia', 'openai', 'gemini'], 'fallback' => 'local'],
        'data_lookup' => ['providers' => ['nvidia', 'gemini'], 'fallback' => 'local'],

        // General purpose — NVIDIA first (fast, good default)
        'general' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'general_assistant' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'chat' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'summarization' => ['providers' => ['nvidia', 'openai', 'gemini'], 'fallback' => 'local'],
        'translation' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'classification' => ['providers' => ['nvidia', 'gemini'], 'fallback' => 'local'],
        'sentiment_analysis' => ['providers' => ['nvidia', 'gemini'], 'fallback' => 'local'],

        // CRM/business workflows
        'email_draft' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'lead_email' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'crm_summary' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'customer_summary' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'supplier_analysis' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'business_summary' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'insights' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'document_analysis' => ['providers' => ['gemini', 'nvidia', 'openai'], 'fallback' => 'local'],
        'inventory_analysis' => ['providers' => ['nvidia', 'gemini'], 'fallback' => 'local'],
        'procurement_analysis' => ['providers' => ['nvidia', 'gemini', 'openai'], 'fallback' => 'local'],
        'finance_analysis' => ['providers' => ['nvidia', 'openai', 'gemini'], 'fallback' => 'local'],
    ],

    /*
    | Claude specialist gate. Claude is ONLY used for high-value complex
    | reasoning. The decision engine escalates a task to Claude when it is on
    | the complex list AND its business impact score reaches the threshold.
    */
    'claude' => [
        'min_impact_score' => (int) env('AI_CLAUDE_MIN_IMPACT', 70),
    ],

    /*
    | Business impact score per task (0-100). The AI Decision Engine uses this
    | with the task type, context size and cache availability. It never decides
    | alone, and scores never gate the cheaper levels (0-2) which always run.
    */
    'impact_scores' => [
        'email_draft' => 25,
        'lead_email' => 25,
        'crm_summary' => 30,
        'general_assistant' => 30,
        'customer_summary' => 35,
        'supplier_analysis' => 40,
        'business_summary' => 40,
        'insights' => 45,
        'inventory_analysis' => 45,
        'document_analysis' => 50,
        'finance_analysis' => 55,
        'procurement_analysis' => 60,
        'complex_document_analysis' => 75,
        'deep_customer_analysis' => 80,
        'opportunity_analysis' => 80,
        'complex_business_question' => 85,
        'strategic_recommendation' => 85,
        'deep_supplier_analysis' => 85,
        'negotiation_strategy' => 85,
        'supplier_comparison' => 90,
        'daily_priorities' => 90,
        'executive_review' => 95,
    ],

    /*
    | Tasks that require Claude-grade reasoning. Anything not listed here is
    | answered by local rules, DB queries, cache or a simple AI provider.
    */
    'complex_tasks' => [
        'deep_supplier_analysis',
        'negotiation_strategy',
        'deep_customer_analysis',
        'opportunity_analysis',
        'supplier_comparison',
        'executive_review',
        'daily_priorities',
        'complex_document_analysis',
        'complex_business_question',
        'strategic_recommendation',
    ],

    /*
    | Provider health. A provider that fails repeatedly within the window is
    | temporarily skipped by the router so the ERP never depends on a broken
    | provider. A success resets the counter.
    */
    'health' => [
        'max_failures' => (int) env('AI_HEALTH_MAX_FAILURES', 3),
        'window_seconds' => (int) env('AI_HEALTH_WINDOW', 600),
    ],

    /*
    | Provider adapters.
    | auth_mode: 'rapidapi' -> x-rapidapi-key + x-rapidapi-host headers,
    |            'bearer'   -> Authorization: Bearer <key>.
    | path '/' targets the RapidAPI host root (DeepSeek).
    */
    'providers' => [
        // Disabled — swift-ai.p.rapidapi.com is unreliable/unavailable.
        // Re-enable only with a valid RapidAPI key.
        'swift' => [
            'base_url' => env('SWIFT_AI_ENDPOINT', 'https://swift-ai.p.rapidapi.com/chat/completions'),
            'host' => env('SWIFT_AI_HOST', 'swift-ai.p.rapidapi.com'),
            'path' => env('SWIFT_AI_PATH', '/chat/completions'),
            'model' => env('SWIFT_AI_MODEL', 'gpt-5'),
            'api_key' => env('RAPIDAPI_KEY', ''),
            'auth_mode' => 'rapidapi',
            'enabled' => false,
        ],
        'gemini' => [
            'base_url' => env('GEMINI_AI_ENDPOINT', 'https://generativelanguage.googleapis.com'),
            'host' => 'generativelanguage.googleapis.com',
            'path' => '/v1beta/models/{model}:generateContent',
            'model' => env('GEMINI_AI_MODEL', 'gemini-2.5-flash'),
            'api_key' => env('GEMINI_API_KEY', ''),
            'auth_mode' => 'google',
        ],
        'deepseek' => [
            'base_url' => env('DEEPSEEK_AI_ENDPOINT', 'https://deepseek-v31.p.rapidapi.com/'),
            'host' => env('DEEPSEEK_AI_HOST', 'deepseek-v31.p.rapidapi.com'),
            'path' => env('DEEPSEEK_AI_PATH', '/'),
            'model' => env('DEEPSEEK_AI_MODEL', 'DeepSeek-V3.2'),
            'api_key' => env('RAPIDAPI_KEY', ''),
            'auth_mode' => 'rapidapi',
        ],
        // Disabled — claude-3-5-sonnet.p.rapidapi.com is a fake/unreliable endpoint.
        // Use the official 'anthropic' provider with a direct API key instead.
        'claude' => [
            'base_url' => env('CLAUDE_RAPIDAPI_ENDPOINT', 'https://claude-3-5-sonnet.p.rapidapi.com/'),
            'host' => env('CLAUDE_RAPIDAPI_HOST', 'claude-3-5-sonnet.p.rapidapi.com'),
            'path' => env('CLAUDE_RAPIDAPI_PATH', '/'),
            'model' => env('CLAUDE_MODEL', 'claude-3-5-sonnet'),
            'api_key' => env('CLAUDE_RAPIDAPI_KEY', env('RAPIDAPI_KEY', '')),
            'auth_mode' => 'rapidapi',
            'enabled' => false,
        ],
        'nvidia' => [
            'base_url' => env('NVIDIA_BASE_URL', 'https://integrate.api.nvidia.com'),
            'host' => 'integrate.api.nvidia.com',
            'path' => '/v1/chat/completions',
            'model' => env('NVIDIA_MODEL', 'nvidia/llama-3.3-nemotron-super-49b-v1.5'),
            'api_key' => env('NVIDIA_API_KEY', ''),
            'auth_mode' => 'bearer',
        ],
        'openai' => [
            'base_url' => 'https://api.openai.com/v1/chat/completions',
            'host' => 'api.openai.com',
            'path' => '/v1/chat/completions',
            'model' => env('OPENAI_MODEL', 'gpt-4o'),
            'api_key' => env('OPENAI_API_KEY', ''),
            'auth_mode' => 'bearer',
        ],
        'anthropic' => [
            'base_url' => 'https://api.anthropic.com/v1/messages',
            'host' => 'api.anthropic.com',
            'path' => '/v1/messages',
            'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-4-20250514'),
            'api_key' => env('ANTHROPIC_API_KEY', ''),
            'auth_mode' => 'bearer',
        ],
    ],

    /*
    | Estimated cost per million tokens [input, output] in USD. Used only to
    | estimate spend on the AI usage dashboard; figures are estimates.
    */
    'cost_per_mtok' => [
        'swift' => [0.5, 1.5],
        'gemini' => [0.2, 0.7],
        'deepseek' => [0.27, 1.1],
        'nvidia' => [0.2, 0.6],
        'openai' => [2.5, 10.0],
        'anthropic' => [3.0, 15.0],
        'claude' => [3.0, 15.0],
    ],

    /*
    | Providers admins can connect from the settings UI. Each entry drives the
    | connection form (model suggestions + purpose) without any .env editing.
    | Onboarding order matters — the first provider card is highlighted.
    */
    'ui_providers' => [
        'gemini' => [
            'label' => 'Google Gemini',
            'description' => "Google's fast, strong-reasoning model. Great for complex analysis, summaries and document understanding.",
            'key_help' => 'Paste your Google AI Studio API key (starts with "AIza").',
            'default_model' => 'gemini-2.5-flash',
            'models' => ['gemini-2.5-flash', 'gemini-2.5-pro', 'gemini-2.0-flash', 'gemini-1.5-flash'],
            'docs_url' => 'https://aistudio.google.com/app/apikey',
        ],
        'nvidia' => [
            'label' => 'NVIDIA NIM',
            'description' => "NVIDIA's hosted model catalogue (built for code, reasoning and fast general answers).",
            'key_help' => 'Paste your NVIDIA API key (starts with "nvapi-").',
            'default_model' => 'nvidia/llama-3.3-nemotron-super-49b-v1.5',
            'models' => ['nvidia/llama-3.3-nemotron-super-49b-v1.5', 'nvidia/llama-3.1-nemotron-ultra-253b-v1.0', 'meta/llama-3.1-8b-instruct'],
            'docs_url' => 'https://org.ngc.nvidia.com/setup/api-key',
        ],
        'openai' => [
            'label' => 'OpenAI',
            'description' => 'GPT model catalogue. Flexible general-purpose generation.',
            'key_help' => 'Paste your OpenAI API key (starts with "sk-").',
            'default_model' => 'gpt-4o',
            'models' => ['gpt-4o', 'gpt-4o-mini', 'gpt-4.1', 'gpt-4.1-mini'],
            'docs_url' => 'https://platform.openai.com/api-keys',
        ],
        'anthropic' => [
            'label' => 'Anthropic Claude',
            'description' => 'Claude model catalogue for high-value reasoning and analysis.',
            'key_help' => 'Paste your Anthropic API key (starts with "sk-ant-").',
            'default_model' => 'claude-sonnet-4-20250514',
            'models' => ['claude-sonnet-4-20250514', 'claude-opus-4-20250514', 'claude-haiku-4-20250514'],
            'docs_url' => 'https://console.anthropic.com/settings/keys',
        ],
    ],
];
