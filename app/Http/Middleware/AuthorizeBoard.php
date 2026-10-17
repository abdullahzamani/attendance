<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets signed-in users, or a screen that opened the board with the board token, see the display board.
 *
 * The token is checked once and a fingerprint of it is remembered in the session, so the board's
 * polling requests (which don't carry the query string) keep working. Changing BOARD_TOKEN changes
 * the fingerprint, which cuts off every screen still using the old link.
 */
class AuthorizeBoard
{
    public const SESSION_KEY = 'board_access';

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            return $next($request);
        }

        $token = (string) config('board.token');

        if ($token === '') {
            return redirect()->guest(route('filament.admin.auth.login'));
        }

        $fingerprint = hash('sha256', $token);
        $remembered = $request->session()->get(self::SESSION_KEY);

        if (is_string($remembered) && hash_equals($fingerprint, $remembered)) {
            return $next($request);
        }

        $given = $request->query('token');

        if (is_string($given) && hash_equals($token, $given)) {
            $request->session()->put(self::SESSION_KEY, $fingerprint);

            // Drop the token from the address bar so it isn't shown on the TV or kept in history.
            return redirect()->to($request->fullUrlWithoutQuery('token'));
        }

        return redirect()->guest(route('filament.admin.auth.login'));
    }
}
