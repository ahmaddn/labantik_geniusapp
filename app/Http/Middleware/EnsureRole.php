<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware(['role:admin,guru']) or ->middleware(['role:admin','role:guru'])
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        if (empty($roles)) {
            return $next($request);
        }

        // Flatten and split comma-separated role parameters
        $allowed = [];
        foreach ($roles as $r) {
            foreach (array_map('trim', explode(',', $r)) as $part) {
                if ($part !== '') $allowed[] = $part;
            }
        }

        if (!in_array($user->role, $allowed, true)) {
            abort(403);
        }

        return $next($request);
    }
}
