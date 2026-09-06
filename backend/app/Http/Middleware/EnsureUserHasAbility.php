<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasAbility
{
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        abort_unless($request->user()?->hasAbility($ability), 403, 'شما اجازه انجام این عملیات را ندارید.');

        return $next($request);
    }
}
