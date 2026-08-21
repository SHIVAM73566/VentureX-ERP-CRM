<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\SecurityEventService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * GDPR / CCPA Data Deletion Controller
 *
 * Allows users to:
 * 1. Request export of their personal data
 * 2. Request deletion of their account and all personal data
 * 3. Anonymize their data instead of full deletion (for audit trail integrity)
 */
class DataDeletionController extends Controller
{
    /**
     * Show the data deletion request form.
     */
    public function showForm()
    {
        $user = Auth::user();

        return view('auth.data-deletion', [
            'user' => $user,
            'dataSummary' => $this->getUserDataSummary($user),
        ]);
    }

    /**
     * Export all user data as JSON (GDPR right to data portability).
     */
    public function exportData(Request $request)
    {
        $user = $request->user();

        $data = [
            'export_date' => now()->toIso8601String(),
            'reference' => 'VentureX-ERP-2026-01483863236',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'job_title' => $user->job_title,
                'created_at' => $user->created_at,
                'last_login_at' => $user->last_login_at,
            ],
            'audit_logs' => $this->getUserAuditLogs($user),
            'login_attempts' => $this->getUserLoginAttempts($user),
        ];

        SecurityEventService::record(
            'privacy',
            'data_export',
            'User data export requested',
            'info',
            $user
        );

        AuditLogger::log('data_export', 'privacy');

        return response()->json($data, 200, [
            'Content-Disposition' => 'attachment; filename="my-data-export.json"',
        ]);
    }

    /**
     * Delete user account and all personal data.
     *
     * Password confirmation required for security.
     */
    public function deleteAccount(Request $request)
    {
        $request->validate([
            'password' => ['required', 'string'],
            'confirmation' => ['required', 'in:DELETE_MY_ACCOUNT'],
        ]);

        $user = $request->user();

        // Verify password
        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'password' => 'The password is incorrect.',
            ]);
        }

        // Log the deletion request before executing
        SecurityEventService::record(
            'privacy',
            'account_deletion',
            'User account deletion requested and processed',
            'critical',
            $user
        );

        // Anonymize user data instead of hard delete (preserves audit trail)
        $this->anonymizeUser($user);

        // Flush sessions
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success',
            'Your account has been deleted and all personal data has been removed.'
        );
    }

    /**
     * Anonymize user data — replaces all PII with anonymous values.
     * Preserves referential integrity for audit trails.
     */
    protected function anonymizeUser(User $user): void
    {
        $deletedAt = now();

        // Anonymize the user record
        $user->update([
            'name' => '[DELETED USER]',
            'email' => "deleted_{$user->id}_".time().'@removed.local',
            'phone' => null,
            'first_name' => null,
            'last_name' => null,
            'job_title' => null,
            'password' => Hash::make(Str::random(64)),
            'remember_token' => null,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'is_active' => false,
        ]);

        // Soft delete (preserves ID for foreign key references)
        $user->delete();

        // Anonymize login attempts
        DB::table('login_attempts')
            ->where('user_id', $user->id)
            ->update([
                'email' => '[DELETED]',
                'ip' => '0.0.0.0',
                'user_agent' => '[DELETED]',
                'device_fingerprint' => '[DELETED]',
            ]);

        // Anonymize audit logs
        DB::table('audit_logs')
            ->where('user_id', $user->id)
            ->update([
                'ip' => '0.0.0.0',
                'user_agent' => '[DELETED]',
            ]);

        // Anonymize security events
        DB::table('security_events')
            ->where('user_id', $user->id)
            ->update([
                'ip' => '0.0.0.0',
                'user_agent' => '[DELETED]',
            ]);

        // Clear any cached data for this user
        cache()->forget("user:{$user->id}");
        cache()->forget("login_attempts:{$user->id}");
    }

    /**
     * Get a summary of user data for display.
     */
    protected function getUserDataSummary(User $user): array
    {
        return [
            'audit_logs' => DB::table('audit_logs')->where('user_id', $user->id)->count(),
            'login_attempts' => DB::table('login_attempts')->where('user_id', $user->id)->count(),
            'security_events' => DB::table('security_events')->where('user_id', $user->id)->count(),
        ];
    }

    /**
     * Get user's audit logs for export.
     */
    protected function getUserAuditLogs(User $user): array
    {
        return DB::table('audit_logs')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(1000)
            ->get()
            ->toArray();
    }

    /**
     * Get user's login attempts for export.
     */
    protected function getUserLoginAttempts(User $user): array
    {
        return DB::table('login_attempts')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(500)
            ->get()
            ->toArray();
    }
}
