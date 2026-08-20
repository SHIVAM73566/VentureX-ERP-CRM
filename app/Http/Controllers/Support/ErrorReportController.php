<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\ErrorReport;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ErrorReportController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'error_type' => ['required', 'string', 'in:'.implode(',', array_keys(ErrorReport::ERROR_TYPES))],
            'error_message' => ['required', 'string', 'max:5000'],
            'file' => ['nullable', 'string', 'max:500'],
            'line' => ['nullable', 'integer'],
            'stack_trace' => ['nullable', 'string', 'max:50000'],
            'module' => ['nullable', 'string', 'max:128'],
            'controller' => ['nullable', 'string', 'max:128'],
            'action' => ['nullable', 'string', 'max:128'],
            'route' => ['nullable', 'string', 'max:255'],
            'http_method' => ['nullable', 'string', 'max:10'],
            'http_status' => ['nullable', 'integer'],
            'url' => ['nullable', 'string', 'max:2000'],
            'request_data' => ['nullable', 'array'],
            'app_version' => ['nullable', 'string', 'max:32'],
            'php_version' => ['nullable', 'string', 'max:32'],
            'laravel_version' => ['nullable', 'string', 'max:32'],
            'server_software' => ['nullable', 'string', 'max:128'],
        ]);

        $sanitized = array_filter($validated, fn ($v) => $v !== null);

        unset(
            $sanitized['password'],
            $sanitized['api_key'],
            $sanitized['secret'],
            $sanitized['token'],
            $sanitized['credit_card'],
            $sanitized['bank_account'],
            $sanitized['ssn'],
        );

        $sanitized['ip_address'] = $request->ip();
        $sanitized['user_agent'] = mb_substr($request->userAgent() ?? '', 0, 500);
        $sanitized['company_id'] = CompanyContext::id();
        $sanitized['user_id'] = auth()->id();

        $errorHash = ErrorReport::generateErrorHash(
            $sanitized['error_message'],
            $sanitized['file'] ?? '',
            $sanitized['line'] ?? 0
        );

        $existing = ErrorReport::where('error_hash', $errorHash)->first();

        if ($existing) {
            $existing->update([
                'occurrence_count' => $existing->occurrence_count + 1,
                'last_seen_at' => now(),
            ]);

            return response()->json([
                'status' => 'duplicate',
                'error_id' => $existing->id,
                'occurrence_count' => $existing->occurrence_count,
            ]);
        }

        $sanitized['error_hash'] = $errorHash;
        $sanitized['status'] = 'new';
        $sanitized['occurrence_count'] = 1;
        $sanitized['first_seen_at'] = now();
        $sanitized['last_seen_at'] = now();

        $error = ErrorReport::create($sanitized);

        return response()->json([
            'status' => 'created',
            'error_id' => $error->id,
        ], 201);
    }
}
