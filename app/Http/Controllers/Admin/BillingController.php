<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\Billing\StripeBillingPortalService;
use App\Services\Billing\StripeCheckoutService;
use App\Services\CompanyContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;

class BillingController extends Controller
{
    public function checkout(Request $request, StripeCheckoutService $checkout): RedirectResponse
    {
        $this->authorize('configure');

        $validated = $request->validate([
            'layer_key' => ['required', 'string', 'max:64'],
        ]);

        $layerKey = $validated['layer_key'];
        $layer = config('layers.layers.'.$layerKey);

        // Only purchasable layers explicitly configured on the server.
        if (! is_array($layer) || ($layer['status'] ?? '') !== 'available'
            || blank(config('services.stripe.prices.'.$layerKey))) {
            abort(404, 'Unknown or unavailable add-on.');
        }

        $companyId = CompanyContext::id();
        if ($companyId === null) {
            return redirect()
                ->route('admin.settings.index', ['tab' => 'plan'])
                ->with('error', 'No company context is available for this upgrade.');
        }

        try {
            $session = $checkout->createSession($companyId, $layerKey, $request->user()?->email);
        } catch (RuntimeException $e) {
            AuditLogger::log('billing_checkout_unavailable', 'billing', null, null, [
                'company_id' => $companyId,
                'layer_key' => $layerKey,
            ]);

            return redirect()
                ->route('admin.settings.index', ['tab' => 'plan'])
                ->with('error', $e->getMessage());
        }

        AuditLogger::log('billing_checkout_started', 'billing', null, null, [
            'company_id' => $companyId,
            'layer_key' => $layerKey,
            'checkout_session_id' => $session['id'],
        ]);

        // Redirect to Stripe-hosted Checkout. No keys or payment data are
        // rendered into application HTML, JS or logs.
        return redirect()->away($session['url']);
    }

    /**
     * Send the admin to Stripe's hosted Billing Portal so they can update
     * payment methods, view invoices or cancel a subscription themselves.
     */
    public function portal(StripeBillingPortalService $portal): RedirectResponse
    {
        $this->authorize('configure');

        $companyId = CompanyContext::id();
        if ($companyId === null) {
            return redirect()
                ->route('admin.settings.index', ['tab' => 'plan'])
                ->with('error', 'No company context is available for billing management.');
        }

        try {
            $session = $portal->createSession($companyId);
        } catch (RuntimeException $e) {
            return redirect()
                ->route('admin.settings.index', ['tab' => 'plan'])
                ->with('error', $e->getMessage());
        }

        AuditLogger::log('billing_portal_opened', 'billing', null, null, [
            'company_id' => $companyId,
        ]);

        return redirect()->away($session['url']);
    }
}
