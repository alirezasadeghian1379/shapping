<?php

use App\Http\Middleware\AuthenticateApiToken;
use App\Http\Middleware\AuditAdminActivity;
use App\Http\Middleware\EnsureUserHasAbility;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'auth.api' => AuthenticateApiToken::class,
            'ability' => EnsureUserHasAbility::class,
            'audit.admin' => AuditAdminActivity::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $exception, Request $request) {
            return $request->expectsJson() ? response()->json(['message' => 'منبع درخواستی پیدا نشد.'], 404) : null;
        });
        $exceptions->render(function (AuthorizationException $exception, Request $request) {
            return $request->expectsJson() ? response()->json(['message' => 'اجازه انجام این عملیات را ندارید.'], 403) : null;
        });
        $exceptions->render(function (MethodNotAllowedHttpException $exception, Request $request) {
            return $request->expectsJson() ? response()->json(['message' => 'روش ارسال درخواست برای این مسیر مجاز نیست.'], 405) : null;
        });
        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request) {
            return $request->expectsJson() ? response()->json(['message' => 'تعداد درخواست‌ها بیش از حد مجاز است؛ کمی بعد دوباره تلاش کنید.'], 429) : null;
        });
    })->create();
