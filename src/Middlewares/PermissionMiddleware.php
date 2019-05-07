<?php

namespace Metko\Metkontrol\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Auth\Access\AuthorizationException;
use Metko\Metkontrol\Exceptions\UnauthorizedException;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $permission)
    {
        if (Auth::guest()) {
            throw UnauthorizedException::notLoggedIn();
        }
        $permission = is_array($permission)
            ? $permission
            : explode('|', $permission);
        if (! Auth::user()->hasAnyPermission($permission)) {
            throw UnauthorizedException::forPermission($permission);
        }
        return $next($request);
    }
}
