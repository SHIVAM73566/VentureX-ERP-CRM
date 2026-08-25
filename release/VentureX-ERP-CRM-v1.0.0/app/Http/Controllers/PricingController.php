<?php

namespace App\Http\Controllers;

use App\Models\CustomerLicense;
use App\Models\LicensePurchase;
use App\Models\User;
use App\Services\LicenseManager;
use App\Services\PayoneerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use RuntimeException;
use Throwable;

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
        $email = trim((string) ($payment['buyer_email'] ?? ''));
        $buyerName = trim((string) ($payment['buyer_name'] ?? '')) ?: 'Customer';
        $licenseTier = (string) ($payment['metadata']['license_tier'] ?? '');
        $reference = trim((string) ($payment['reference'] ?? ''));
        $amount = (float) ($payment['amount'] ?? 0);
        $currency = strtoupper((string) ($payment['currency'] ?? 'USD'));

        Log::info('Payoneer payment completed', [
            'email' => $email,
            'tier' => $licenseTier,
            'reference' => $reference,
            'amount' => $amount,
        ]);

        if ($email === '' || $licenseTier === '') {
            Log::error('Payoneer payment completed with missing buyer data', [
                'email' => $email,
                'tier' => $licenseTier,
                'reference' => $reference,
            ]);

            return;
        }

        if ($reference === '') {
            $reference = 'VX-'.strtoupper(uniqid());
        }

        // Webhooks can be delivered more than once — only process a reference once.
        $purchase = LicensePurchase::firstOrCreate(
            ['reference' => $reference],
            [
                'email' => $email,
                'name' => $buyerName,
                'tier' => $licenseTier,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'completed',
                'purchased_at' => now(),
            ]
        );

        if (! $purchase->wasRecentlyCreated) {
            Log::info('Payoneer payment already processed', ['reference' => $reference]);

            return;
        }

        $this->provisionLicense($purchase);
    }

    /**
     * Generate and attach the license key, activate it for an existing
     * customer company when possible, and email the buyer.
     */
    private function provisionLicense(LicensePurchase $purchase): void
    {
        $domain = parse_url((string) config('app.url'), PHP_URL_HOST)
            ?: request()->getHost()
            ?: '';

        $licenseKey = null;

        try {
            $licenseKey = LicenseManager::generateKey($domain, $purchase->tier);
            $purchase->update(['license_key' => $licenseKey]);
        } catch (Throwable $e) {
            Log::error('License key generation failed', [
                'reference' => $purchase->reference,
                'error' => $e->getMessage(),
            ]);
        }

        if ($licenseKey !== null) {
            $this->activateForExistingCustomer($purchase, $licenseKey);
        }

        $this->sendConfirmationEmail($purchase, $licenseKey);
    }

    /**
     * Attach the license to the buyer's company account, if they have one.
     */
    private function activateForExistingCustomer(LicensePurchase $purchase, string $licenseKey): void
    {
        $user = User::where('email', $purchase->email)->first();

        if (! $user?->company_id) {
            return;
        }

        // Checkout tier "standard" is stored as "starter" on customer licenses.
        $tier = $purchase->tier === 'standard' ? 'starter' : $purchase->tier;

        $limits = match ($tier) {
            'professional' => ['max_users' => 25, 'max_companies' => 3],
            'enterprise' => ['max_users' => 999999, 'max_companies' => 999999],
            default => ['max_users' => 5, 'max_companies' => 1],
        };

        try {
            CustomerLicense::updateOrCreate(
                ['company_id' => $user->company_id, 'tier' => $tier],
                [
                    'license_key' => $licenseKey,
                    'status' => 'active',
                    'activated_at' => now(),
                    'expires_at' => null,
                    'domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
                    'ip_address' => request()->ip(),
                    'last_check_at' => now(),
                    ...$limits,
                ]
            );
        } catch (Throwable $e) {
            Log::error('Failed to activate customer license record', [
                'reference' => $purchase->reference,
                'company_id' => $user->company_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Email the buyer their receipt and license key.
     */
    private function sendConfirmationEmail(LicensePurchase $purchase, ?string $licenseKey): void
    {
        $tierName = config("payoneer.licenses.{$purchase->tier}.name", ucfirst($purchase->tier).' License');

        $body = "Hi {$purchase->name},\n\n"
            ."Thank you for your purchase of ".config('app.name')."!\n\n"
            ."Product: {$tierName}\n"
            ."Order reference: {$purchase->reference}\n"
            ."Amount paid: {$purchase->currency} ".number_format((float) $purchase->amount, 2)."\n"
            ."\n";

        if ($licenseKey) {
            $body .= "Your license key:\n\n    {$licenseKey}\n\n"
                ."You can activate it under Settings → License in your dashboard.\n\n";
        } else {
            $body .= "Your license key will be issued shortly and sent in a follow-up email.\n\n";
        }

        $body .= 'If you have any questions, simply reply to this email.'."\n\n"
            .'— The '.config('app.name').' Team';

        $appName = (string) config('app.name', 'VentureX ERP');

        try {
            Mail::raw($body, function ($message) use ($purchase, $tierName, $appName) {
                $message->to($purchase->email)
                    ->subject("[{$appName}] Your {$tierName} is confirmed");
            });
        } catch (Throwable $e) {
            // Never fail the webhook because mail delivery failed.
            Log::error('Failed to send license confirmation email', [
                'reference' => $purchase->reference,
                'email' => $purchase->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
