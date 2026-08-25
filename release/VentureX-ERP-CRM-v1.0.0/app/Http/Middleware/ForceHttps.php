<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only enforced in production; local development stays on HTTP.
        if (! app()->isProduction()) {
            return $next($request);
        }

        if (! $request->isSecure() && ! $request->is('up') && ! $request->is('paypal/webhook')) {
            return redirect()->secure($request->getRequestUri());
        }

        return $next($request);
    }
}
