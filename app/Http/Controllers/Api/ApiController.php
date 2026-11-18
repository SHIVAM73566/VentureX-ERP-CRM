<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CompanyContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class ApiController extends Controller
{
    protected function companyId(): int
    {
        return CompanyContext::id();
    }

    /**
     * Existence rule scoped to the current company to prevent cross-tenant references.
     */
    protected function companyExists(string $table, string $column = 'id'): Rule
    {
        return Rule::exists($table, $column)
            ->where('company_id', auth()->user()->company_id);
    }

    protected function successResponse($data = null, string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function errorResponse(string $message = 'Error', int $code = 400, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Clamp the requested page size before running a paginated query.
     */
    protected function perPage(Request $request, int $default = 15): int
    {
        return max(1, min(100, $request->integer('per_page', $default)));
    }

    /**
     * Central response guard: even callers that build their paginator from an
     * unvalidated request cannot return more than 100 records per page.
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, string $message = 'Success'): JsonResponse
    {
        $perPage = max(1, min(100, $paginator->perPage()));

        if ($perPage !== $paginator->perPage()) {
            $paginator = new LengthAwarePaginator(
                array_slice($paginator->items(), 0, $perPage),
                $paginator->total(),
                $perPage,
                $paginator->currentPage(),
                ['path' => $paginator->path()]
            );
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $paginator->items(),
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }
}
