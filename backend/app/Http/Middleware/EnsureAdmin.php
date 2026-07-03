<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Rol ve kullanıcı yönetimi yalnızca admin rolündeki kullanıcılara açıktır. */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAdmin()) {
            return response()->json([
                'message' => 'Bu işlem için yönetici yetkisi gereklidir.',
            ], 403);
        }

        return $next($request);
    }
}
