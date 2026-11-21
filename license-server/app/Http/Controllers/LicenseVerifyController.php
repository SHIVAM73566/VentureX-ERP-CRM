<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseVerifyController extends Controller
{
    /**
     * POST /api/license/verify
     * TODO: authenticate request (API key / signed challenge), look up customer/installation,
     * return ed25519-signed token with entitlements + expiry.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'installation_id' => ['nullable', 'string', 'max:255'],
            'customer_key' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json([
            'message' => 'Scaffold only. Implement token issuance with Ed25519 signing.',
            'offline_grace_days' => (int) env('LICENSE_OFFLINE_GRACE_DAYS', 14),
        ], 501);
    }
}
