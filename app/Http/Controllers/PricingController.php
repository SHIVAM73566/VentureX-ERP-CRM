<?php

namespace App\Http\Controllers;

use App\Services\PayoneerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PricingController extends Controller
{
    public function __construct(
        private PayoneerService $payoneer
    ) {}

    /**
     * Show pricing page with 3 license tiers.
     */
    public function index()
    {
        $licenses = $this->payoneer->getLicenses();
        $isConfigured = $this->payoneer->isConfigured();
        $payoneerEmail = $this->payoneer->getEmail();

        return view('pricing.index', compact('licenses', 'isConfigured', 'payoneerEmail'));
    }

    /**
     * Create a Payoneer checkout and show payment details.
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'license_tier' => 'required|string|in:standard,professional,enterprise',
            'email' => 'required|email|max:255',
            'name' => 'required|string|max:255',
        ]);

        try {
            $session = $this->payoneer->createCheckoutSession(
                $validated['email'],
                $validated['name'],
                $validated['license_tier']
            );

            // API mode: redirect to Payoneer checkout
            if ($session['type'] === 'api' && ! empty($session['url'])) {
                return redirect($session['url']);
            }

            // Manual mode: show payment instructions page
            return view('pricing.payment', [
                'session' => $session,
                'tier' => $validated['license_tier'],
            ]);
        } catch (RuntimeException $e) {
            Log::error('Payoneer checkout failed', [
                'user_id' => Auth::id(),
                'tier' => $validated['license_tier'],
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to create payment session. Please try again.');
        }
    }

    /**
     * Show success page after successful payment.
     */
    public function success(Request $request)
    {
        $reference = $request->query('reference', '');

        return view('pricing.success', compact('reference'));
    }

    /**
     * Show cancellation page.
     */
    public function cancel()
    {
        return view('pricing.cancel');
    }

    /**
     * Handle Payoneer webhooks for payment events.
     */
    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Payoneer-Signature', '');

        if (! $this->payoneer->verifyWebhook($payload, $signature)) {
            Log::warning('Payoneer webhook: invalid signature');

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $event = json_decode($payload, true);

        if (! $event) {
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        $eventType = $event['type'] ?? '';
        Log::info('Payoneer webhook received', ['event_type' => $eventType]);

        match ($eventType) {
            'payment.completed' => $this->handlePaymentCompleted($event),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }

    /**
     * Handle successful payment completion.
     */
    private function handlePaymentCompleted(array $event): void
    {
        $payment = $event['data'] ?? [];
        $email = $payment['buyer_email'] ?? '';
        $licenseTier = $payment['metadata']['license_tier'] ?? '';
        $reference = $payment['reference'] ?? '';
        $amount = $payment['amount'] ?? 0;

        Log::info('Payoneer payment completed', [
            'email' => $email,
            'tier' => $licenseTier,
            'reference' => $reference,
            'amount' => $amount,
        ]);

        // TODO: Store license purchase record, send confirmation email,
        // provision license key via LicenseManager, etc.
    }
}
