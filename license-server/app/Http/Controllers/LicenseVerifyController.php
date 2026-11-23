<?php

namespace App\Http\Controllers;

use App\Services\LicenseTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LicenseVerifyController extends Controller
{
    public function __construct(protected LicenseTokenService $tokens) {}

    /**
     * POST /api/license/verify
     *
     * Authenticated with a server-to-server API key. Installations are mapped
     * from LICENSE_INSTALLATIONS (JSON) in this server's .env; no customer
     * data is hard-coded in source.
     */
    public function verify(Request $request): JsonResponse
    {
        $expectedKey = (string) env('LICENSE_VERIFY_API_KEY', '');
        $providedKey = (string) ($request->header('X-License-Api-Key') ?? '');
        if ($expectedKey === '' || $providedKey === '' || ! hash_equals($expectedKey, $providedKey)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $data = $request->validate([
            'installation_id' => ['required', 'string', 'max:255'],
            'customer_key' => ['required', 'string', 'max:255'],
            'company_id' => ['nullable', 'integer', 'min:1'],
        ]);

        $installations = json_decode((string) env('LICENSE_INSTALLATIONS', '{}'), true);
        $record = is_array($installations) ? ($installations[$data['installation_id']] ?? null) : null;
        if (! is_array($record)
            || ! hash_equals((string) ($record['customer_key'] ?? ''), $data['customer_key'])) {
            return response()->json(['message' => 'Unknown installation'], 404);
        }

        $companyId = (int) ($data['company_id'] ?? ($record['company_id'] ?? 0));
        if ($companyId < 1) {
            return response()->json(['message' => 'Invalid company binding'], 422);
        }

        $token = $this->tokens->issueToken([
            'sub' => (string) ($record['customer_id'] ?? $data['installation_id']),
            'lic' => (string) ($record['license_id'] ?? $data['installation_id']),
            'company_id' => $companyId,
            'installation_id' => $data['installation_id'],
            'customer_key' => $data['customer_key'],
            'entitlements' => is_array($record['entitlements'] ?? null) ? $record['entitlements'] : [],
        ]);

        return response()->json([
            'token' => $token,
            'offline_grace_days' => max(1, (int) env('LICENSE_OFFLINE_GRACE_DAYS', 14)),
        ]);
    }
}
