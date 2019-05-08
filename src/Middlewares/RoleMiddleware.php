<?php

namespace Metko\Metkontrol\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Metko\Metkontrol\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $role)
    {
        if (Auth::guest()) {
            throw UnauthorizedException::notLoggedIn();
        }
        $roles = is_array($role)
            ? $role
            : explode('|', $role);
        if (!Auth::user()->hasAnyRole($roles)) {
            throw UnauthorizedException::forRoles($roles);
        }

        return $next($request);
    }
}
