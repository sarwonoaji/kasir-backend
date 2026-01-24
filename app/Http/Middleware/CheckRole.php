<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $userRole = $request->user()?->role;

        if (!$userRole || !in_array($userRole, $roles)) {
            return response()->json(['message' => 'Unauthorized role'], 403);
        }

        return $next($request);
    }
}
