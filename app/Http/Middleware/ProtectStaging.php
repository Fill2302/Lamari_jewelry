<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ProtectStaging
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.staging_protected')) {
            return $next($request);
        }

        $username = (string) config('app.staging_username');
        $password = (string) config('app.staging_password');

        if ($username === '' || $password === '') {
            abort(503, 'Staging access is not configured.');
        }

        if (! hash_equals($username, (string) $request->getUser())
            || ! hash_equals($password, (string) $request->getPassword())) {
            return response('Authentication required.', 401, [
                'WWW-Authenticate' => 'Basic realm="Lamari staging"',
                'X-Robots-Tag' => 'noindex, nofollow, noarchive',
            ]);
        }

        $response = $next($request);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        return $response;
    }
}
