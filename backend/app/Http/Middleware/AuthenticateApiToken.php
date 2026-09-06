<?php

namespace App\Http\Middleware;

use App\Models\AccessToken;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $plain = $request->bearerToken();
        $token = $plain ? AccessToken::query()->with('user')->where('token', hash('sha256', $plain))->first() : null;
        if (! $token || ($token->expires_at && $token->expires_at->isPast()) || ! $token->user->is_active) {
            return response()->json(['message' => 'برای ادامه وارد حساب کاربری شوید.'], 401);
        }
        $token->forceFill(['last_used_at' => now()])->save();
        $request->setUserResolver(fn () => $token->user);
        $request->attributes->set('access_token', $token);

        return $next($request);
    }
}
