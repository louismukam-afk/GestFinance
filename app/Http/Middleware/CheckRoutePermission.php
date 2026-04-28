<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoutePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = optional($request->route())->getName()
            ?: $request->method() . ' ' . optional($request->route())->uri();

        if (!$user || $this->isAccessControlRoute($routeName) || $user->canAccessRoute($routeName)) {
            return $next($request);
        }

        abort(403, "Vous n'avez pas l'autorisation d'acceder a cette action.");
    }

    private function isAccessControlRoute(?string $routeName): bool
    {
        return $routeName && (
            str_starts_with($routeName, 'access.')
            || str_starts_with($routeName, 'audit.')
            || in_array($routeName, ['home', 'dashboard', 'logout'], true)
        );
    }
}
