<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && Schema::hasTable('activity_logs') && $this->shouldLog($request)) {
            ActivityLog::create([
                'user_id' => $request->user()->id,
                'user_name' => $request->user()->name,
                'method' => $request->method(),
                'route_name' => optional($request->route())->getName()
                    ?: $request->method() . ' ' . optional($request->route())->uri(),
                'uri' => $request->path(),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
                'payload' => $this->cleanPayload($request),
                'status_code' => $response->getStatusCode(),
                'created_at' => now(),
            ]);
        }

        return $response;
    }

    private function shouldLog(Request $request): bool
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return true;
        }

        $routeName = optional($request->route())->getName()
            ?: $request->method() . ' ' . optional($request->route())->uri();

        return $routeName && (
            str_contains($routeName, 'export')
            || str_contains($routeName, 'pdf')
            || str_contains($routeName, 'excel')
            || str_contains($routeName, 'download')
        );
    }

    private function cleanPayload(Request $request): array
    {
        return collect($request->except([
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'current_password',
        ]))->map(function ($value) {
            if (is_array($value)) {
                return array_slice($value, 0, 50, true);
            }

            return is_string($value) ? substr($value, 0, 255) : $value;
        })->toArray();
    }
}
