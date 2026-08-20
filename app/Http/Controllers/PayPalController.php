<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class PayPalController extends Controller
{
    public function __construct(
        private PayPalService $paypal
    ) {}

    /**
     * Show pricing page with PayPal buttons.
     */
    public function index()
    {
        $plans = config('paypal.plans');
        $clientId = $this->paypal->getClientId();
        $isConfigured = $this->paypal->isConfigured();

        return view('paypal.index', compact('plans', 'clientId', 'isConfigured'));
    }

    /**
     * Show the "Send Payment" page for feature requests / donations.
     */
    public function sendPayment()
    {
        $minAmount = config('paypal.feature_request.min_amount_cents') / 100;
        $maxAmount = config('paypal.feature_request.max_amount_cents') / 100;
        $clientId = $this->paypal->getClientId();
        $isConfigured = $this->paypal->isConfigured();

        return view('paypal.send', compact('minAmount', 'maxAmount', 'clientId', 'isConfigured'));
    }

    /**
     * Create a standard plan order. Server-side price calculation.
     */
    public function createOrder(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|string|in:starter,professional,enterprise',
        ]);

        try {
            $order = $this->paypal->createOrder($validated['plan'], Auth::id());

            return response()->json(['id' => $order['id']]);
        } catch (RuntimeException $e) {
            Log::error('Payment order creation failed', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);

            return response()->json(['error' => 'Failed to create payment order'], 500);
        }
    }

    /**
     * Create a custom amount order (feature request / donation).
     * Server-side amount validation.
     */
    public function createCustomOrder(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:500',
            'description' => 'required|string|max:500',
        ]);

        $amountCents = (int) round($validated['amount'] * 100);

        $minAmount = config('paypal.feature_request.min_amount_cents');
        $maxAmount = config('paypal.feature_request.max_amount_cents');

        if ($amountCents < $minAmount || $amountCents > $maxAmount) {
            return response()->json([
                'error' => 'Amount must be between $'.($minAmount / 100).' and $'.($maxAmount / 100),
            ], 422);
        }

        try {
            $order = $this->paypal->createCustomOrder(
                $amountCents,
                $validated['description'],
                Auth::id()
            );

            return response()->json(['id' => $order['id']]);
        } catch (RuntimeException $e) {
            Log::error('Custom payment order failed', ['user_id' => Auth::id(), 'error' => $e->getMessage()]);

            return response()->json(['error' => 'Failed to create payment order'], 500);
        }
    }

    /**
     * Capture an approved payment.
     */
    public function captureOrder(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|string|max:100',
        ]);

        try {
            // CRITICAL: Ownership check BEFORE capture to prevent IDOR
            $payment = Payment::where('reference', $validated['order_id'])
                ->where('created_by', Auth::id())
                ->first();

            if (! $payment) {
                return response()->json(['error' => 'Payment not found or unauthorized'], 404);
            }

            $result = $this->paypal->captureOrder($validated['order_id']);

            $captureId = $result['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
            $payment->markCompleted([
                'capture_id' => $captureId,
                'payer' => $result['payer'] ?? null,
            ]);

            return response()->json([
                'success' => true,
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
            ]);
        } catch (RuntimeException $e) {
            Log::error('Payment capture failed', ['order_id' => $validated['order_id'], 'error' => $e->getMessage()]);

            return response()->json(['error' => 'Failed to capture payment'], 500);
        }
    }

    /**
     * PayPal webhook — receives payment notifications.
     * Verifies HMAC signature before processing.
     */
    public function webhook(Request $request)
    {
        $body = $request->getContent();

        $headers = [];
        foreach (['paypal-auth-algo', 'paypal-cert-url', 'paypal-chain-id', 'paypal-id', 'paypal-signature', 'paypal-timestamp', 'paypal-transmission-id'] as $header) {
            $headers[$header] = $request->header($header);
        }

        if (! $this->paypal->verifyWebhookSignature($headers, $body)) {
            Log::warning('PayPal webhook signature verification FAILED');

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = json_decode($body, true);
        $eventType = $payload['event_type'] ?? '';

        Log::info('PayPal webhook verified', ['event_type' => $eventType]);

        match ($eventType) {
            'CHECKOUT.ORDER.APPROVED' => $this->handleOrderApproved($payload),
            'PAYMENT.CAPTURE.COMPLETED' => $this->handleCaptureCompleted($payload),
            'PAYMENT.CAPTURE.DENIED' => $this->handleCaptureDenied($payload),
            'PAYMENT.CAPTURE.REFUNDED' => $this->handleCaptureRefunded($payload),
            default => null,
        };

        return response()->json(['status' => 'ok']);
    }

    private function handleOrderApproved(array $payload): void
    {
        $orderId = $payload['resource']['id'] ?? null;
        if ($orderId) {
            Payment::where('reference', $orderId)->update(['status' => 'completed']);
        }
    }

    private function handleCaptureCompleted(array $payload): void
    {
        $captureId = $payload['resource']['id'] ?? null;
        if ($captureId) {
            Payment::where('reference', $captureId)
                ->where('status', '!=', 'completed')
                ->each(fn ($p) => $p->markCompleted());
        }
    }

    private function handleCaptureDenied(array $payload): void
    {
        $captureId = $payload['resource']['id'] ?? null;
        if ($captureId) {
            Payment::where('reference', $captureId)
                ->each(fn ($p) => $p->markFailed('Denied by PayPal'));
        }
    }

    private function handleCaptureRefunded(array $payload): void
    {
        $captureId = $payload['resource']['id'] ?? null;
        if ($captureId) {
            Payment::where('reference', $captureId)
                ->each(fn ($p) => $p->markRefunded());
        }
    }

    /**
     * Payment success page.
     */
    public function success()
    {
        return view('paypal.success');
    }

    /**
     * Payment cancelled page.
     */
    public function cancel()
    {
        return view('paypal.cancel');
    }

    /**
     * Payment history for current user.
     */
    public function history()
    {
        $payments = Payment::where('created_by', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('paypal.history', compact('payments'));
    }
}
