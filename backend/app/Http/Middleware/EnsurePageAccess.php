<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rol izinlerine göre sayfa bazlı erişim kontrolü.
 * Kullanım: ->middleware('page:personel')
 */
class EnsurePageAccess
{
    public function handle(Request $request, Closure $next, string $page): Response
    {
        if (! $request->user()?->canAccessPage($page)) {
            return response()->json([
                'message' => 'Bu sayfaya erişim yetkiniz bulunmuyor.',
            ], 403);
        }

        return $next($request);
    }
}
