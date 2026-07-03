<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user()) {
            return redirect()->route('portal.login');
        }

        $allowedRoles = explode('|', $role);

        if (! in_array($request->user()->role, $allowedRoles, true)) {
            abort(403, 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}
