<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRoutePermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $routeName = optional($request->route())->getName()
            ?: $request->method() . ' ' . optional($request->route())->uri();

        if (!$user) {
            return $next($request);
        }

        if (!$user->isActive()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => "Votre compte est desactive. Contactez l'administrateur pour l'activer."]);
        }

        if ($this->isAccessControlRoute($routeName) || $user->canAccessRoute($routeName)) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, "Acces non autorise. Votre compte n'a pas encore les permissions necessaires.");
        }

        return redirect()
            ->route('home')
            ->with('error', "Acces non autorise. Votre compte est actif, mais aucun role ou permission ne vous autorise encore cette action.");
    }

    private function isAccessControlRoute(?string $routeName): bool
    {
        return $routeName && (
            str_starts_with($routeName, 'access.')
            || str_starts_with($routeName, 'audit.')
            || in_array($routeName, ['home', 'logout'], true)
        );
    }
}
