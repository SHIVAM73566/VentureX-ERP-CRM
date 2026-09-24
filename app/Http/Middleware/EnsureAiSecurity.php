<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnsureAiSecurity
{
    /**
     * Whether this request targets the AI configuration / documentation surface.
     */
    protected function isProtectedRoute(Request $request): bool
    {
        $name = (string) ($request->route()?->getName() ?? '');
        $uri = (string) $request->getPathInfo();

        if (str_starts_with($name, 'admin.ai-providers')) {
            return true;
        }
        if (str_starts_with($name, 'ai.')) {
            return true;
        }
        if (str_contains($uri, 'ai-provider') || str_contains($uri, 'ai/')) {
            return true;
        }

        return $name === 'docs' || $uri === '/docs';
    }

    public function handle(Request $request, Closure $next): Response
    {
        $password = (string) config('ai.docs_password', '');

        // Documentation protection: when AI_DOCS_PASSWORD is configured the AI
        // configuration and documentation pages are locked behind HTTP Basic.
        if ($password !== '' && $this->isProtectedRoute($request)
            && ! $this->verified($request, $password)) {
            return $this->challenge();
        }

        $response = $next($request);

        // Defense-in-depth: provider keys are encrypted at rest and never
        // rendered, but this last-line guard redacts anything that slips into
        // an AI page's HTML output and flags it via response headers.
        if ($this->isProtectedRoute($request)) {
            $response->headers->set('X-Ai-Security', 'enforced');

            $scannable = method_exists($response, 'getContent')
                && ! $response instanceof BinaryFileResponse
                && ! $response instanceof StreamedResponse;
            $content = $scannable ? $response->getContent() : null;
            if (is_string($content) && $content !== '') {
                $redacted = preg_replace(
                    '/(nvapi-[A-Za-z0-9_-]{10,}|AIza[A-Za-z0-9_-]{20,}|sk-[A-Za-z0-9]{16,})/i',
                    '***REDACTED***',
                    $content
                );
                if ($redacted !== null && $redacted !== $content) {
                    $response->setContent($redacted);
                    $response->headers->set('X-Ai-Redaction', '1');
                }
            }
        }

        return $response;
    }

    protected function verified(Request $request, string $password): bool
    {
        $user = (string) $request->header('PHP_AUTH_USER', '');
        $pw = (string) $request->header('PHP_AUTH_PW', '');

        return $user === 'ai-docs' && hash_equals($password, $pw);
    }

    protected function challenge(): Response
    {
        return response('This documentation is password protected.', 401, [
            'WWW-Authenticate' => 'Basic realm="AI Documentation"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
