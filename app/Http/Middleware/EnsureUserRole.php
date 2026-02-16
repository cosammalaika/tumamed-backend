<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $normalizedUserRole = strtoupper((string) $user->role);
        $normalizedRoles = array_map(static fn (string $role): string => strtoupper($role), $roles);

        if ($roles && ! in_array($normalizedUserRole, $normalizedRoles, true)) {
            abort(403);
        }

        if ($user->role === User::ROLE_PHARMACY && ! $user->pharmacy_id) {
            abort(403, 'Pharmacy user missing pharmacy assignment.');
        }

        return $next($request);
    }
}
