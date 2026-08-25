<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Services\SecurityEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $sessions = DB::table('sessions')
            ->where('user_id', $userId)
            ->latest('last_activity')
            ->get()
            ->map(function ($session) {
                $session->current = $session->id === session()->getId();

                return $session;
            });

        return view('auth.sessions', ['sessions' => $sessions]);
    }

    public function destroy(string $id): RedirectResponse
    {
        $session = DB::table('sessions')->where('id', $id)->first();

        if (! $session) {
            return back()->with('error', 'Session not found.');
        }

        $user = auth()->user();

        // Users may revoke only their own sessions; super_admin may revoke any.
        $isOwn = (int) $session->user_id === (int) $user->id;
        if (! $isOwn && ! $user->hasRole('super_admin')) {
            abort(403, 'You may only terminate your own sessions.');
        }

        if ($session->id === session()->getId()) {
            return back()->with('error', 'Use "Sign out" to terminate your current session.');
        }

        DB::table('sessions')->where('id', $id)->delete();

        AuditLogger::log('session_revoked', 'auth', null, null, ['session_id' => $id]);
        SecurityEventService::record('session', 'session_revoked', 'A session was terminated', $isOwn ? 'info' : 'high', null, null, ['session_id' => $id, 'target_user' => $session->user_id]);

        return back()->with('success', 'Session terminated.');
    }

    public function revokeAll(): RedirectResponse
    {
        $user = auth()->user();
        $userId = $user->id;

        DB::table('sessions')->where('user_id', $userId)->where('id', '!=', session()->getId())->delete();

        AuditLogger::log('sessions_revoked', 'auth');
        SecurityEventService::record('session', 'sessions_revoked', 'All other sessions terminated', 'info');

        return back()->with('success', 'All other sessions terminated.');
    }
}
