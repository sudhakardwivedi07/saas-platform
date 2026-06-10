<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

/**
 * Handles per-request "View as Client" impersonation via a URL token.
 *
 * When an admin opens "View as Client" in a new tab, the new tab carries
 * the SAME session cookie as the original tab (browsers share cookies across
 * tabs). Any attempt to change the session (invalidate / regenerate) issues a
 * new Set-Cookie that updates the shared cookie jar, breaking the original tab.
 *
 * This middleware avoids that entirely:
 *  - The session is NEVER modified.
 *  - Every request from the client-view tab carries ?_imp=TOKEN.
 *  - This middleware validates the token and calls Auth::setUser() for THIS
 *    request only — no session writes, no cookie changes.
 *  - Tab 1 (admin) keeps its session and cookie untouched.
 */
class ImpersonateClientViaToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->query('_imp');

        if ($token && $data = Cache::get('imp_view_' . $token)) {
            // Confirm the currently-authenticated user is the admin who created the token.
            if (auth()->check() && (int) auth()->id() === (int) $data['admin_id']) {
                $client = User::withoutGlobalScopes()->find($data['client_id']);

                if ($client) {
                    // Swap the auth user for this request only — NO session changes.
                    Auth::setUser($client);

                    // Share impersonation context with all views rendered in this request.
                    View::share('__imp', [
                        'token'        => $token,
                        'client_name'  => $data['client_name'],
                        'client_email' => $data['client_email'],
                        'admin_id'     => $data['admin_id'],
                    ]);
                }
            }
        }

        return $next($request);
    }
}
