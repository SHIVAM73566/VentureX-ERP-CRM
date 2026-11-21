<?php

namespace App\Http\Middleware;

use App\Services\EntitlementService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLayerEntitled
{
    public function __construct(protected EntitlementService $entitlements) {}

    /**
     * Handle an incoming request. Usage: middleware('layer:updates_support')
     */
    public function handle(Request $request, Closure $next, string $layerKey): Response
    {
        if (! $this->entitlements->has($layerKey)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'This feature requires an active layer entitlement: ' . $layerKey,
                ], 403);
            }

            abort(403, 'This feature is not available on your current plan/layer.');
        }

        return $next($request);
    }
}
