<?php

namespace App\Http\Middleware;

use App\Services\Ai\SuperBrainShield as Shield;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Activates the AERO X Super Intelligence Brain shield on AI traffic.
 *
 * The shield only inspects AI routes and only after at least one provider key
 * is configured, so ordinary ERP pages keep their normal latency.
 */
class SuperBrainShield
{
    public function __construct(protected Shield $shield) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isAiRequest($request) || ! $this->shield->isActive()) {
            return $next($request);
        }

        $decision = $this->shield->inspectRequest($request);

        if (! $decision['allowed']) {
            if ($request->expectsJson() || $request->is('ai/*')) {
                return response()->json([
                    'success' => false,
                    'message' => $decision['reason'],
                    'shield' => 'AERO X Super Intelligence Brain',
                ], $decision['status']);
            }

            return back()->with('error', $decision['reason']);
        }

        $response = $next($request);

        if ($response instanceof Response) {
            $response->headers->set('X-SuperBrain-Shield', 'active');
            $response->headers->set('X-Ai-Security', 'enforced');
        }

        return $response;
    }

    protected function isAiRequest(Request $request): bool
    {
        $name = (string) ($request->route()?->getName() ?? '');
        $uri = (string) $request->getPathInfo();

        return str_starts_with($name, 'ai.')
            || str_starts_with($name, 'admin.settings.ai-key')
            || str_contains($uri, '/ai/')
            || str_ends_with($uri, '/ai')
            || str_contains($uri, 'ai-key')
            || str_contains($uri, 'ai-providers');
    }
}
