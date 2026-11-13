<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Autonomous Self-Healing
    |--------------------------------------------------------------------------
    | When enabled, the SelfHealing middleware intercepts unexpected runtime
    | exceptions, records them in the Error Center + Audit Log, and (outside
    | of debug mode) renders a safe fallback page instead of a blank 500.
    |
    | Validation, authentication, authorization and CSRF exceptions are never
    | swallowed: they keep their normal Laravel behaviour so forms and the
    | login flow continue to work exactly as before.
    */
    'enabled' => (bool) env('SELF_HEALING_ENABLED', true),

    // Render a friendly fallback page/JSON payload outside of debug mode.
    'render_fallback' => (bool) env('SELF_HEALING_RENDER_FALLBACK', true),

    // Deduplicate identical fingerprints for this many seconds.
    'dedupe_seconds' => (int) env('SELF_HEALING_DEDUPE_SECONDS', 60),

    // Maximum stack trace characters stored in the database.
    'max_trace_chars' => (int) env('SELF_HEALING_MAX_TRACE', 12000),

    // Maximum characters of request data retained (sensitive keys stripped).
    'max_request_chars' => (int) env('SELF_HEALING_MAX_REQUEST', 4000),

    // Audit log module name used by /admin/audit-logs.
    'audit_module' => 'system',

];
