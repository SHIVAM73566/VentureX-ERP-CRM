<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Heavy task queueing
    |--------------------------------------------------------------------------
    | Exports, bulk imports and PDF rendering are queued when this flag is
    | enabled. It defaults to false so existing synchronous behaviour (and
    | the test suite's sync queue driver) remains unchanged.
    */
    'queue_heavy_tasks' => env('VENTUREX_QUEUE_HEAVY_TASKS', false),
];
