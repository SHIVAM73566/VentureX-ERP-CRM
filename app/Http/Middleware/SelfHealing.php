<?php

namespace App\Http\Middleware;

use App\Services\SelfHealingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Real-time exception interceptor.
 *
 * Runs at the outermost edge of the HTTP stack. Unexpected 404/500-class
 * exceptions are captured by SelfHealingService. In debug mode they are
 * re-thrown so developers still see the full error page; outside debug the
 * service renders a safe fallback so users never see a blank 500.
 *
 * Validation, auth, authorization and CSRF exceptions are always re-thrown so
 * Laravel's normal redirect/JSON behaviour is preserved.
 */
class SelfHealing
{
    public function __construct(protected SelfHealingService $healing) {}

    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            if (! (bool) config('self-healing.enabled', true)) {
                throw $e;
            }

            $status = $this->healing->statusFor($e);
            $result = $this->healing->capture($e, $request, $status);

            if ($this->healing->mustRethrow($e)) {
                throw $e;
            }

            // A 4xx other than 404 is a client/authorization concern, not a
            // server fault: let Laravel render it normally.
            if ($status < 500 && $status !== 404) {
                throw $e;
            }

            if ((bool) config('app.debug') || ! (bool) config('self-healing.render_fallback', true)) {
                throw $e;
            }

            return $this->healing->fallback($request, $status, $result['fingerprint']);
        }
    }
}
