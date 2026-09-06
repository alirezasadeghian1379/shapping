<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class AuditAdminActivity
{
    private const SENSITIVE_KEYS = [
        'password', 'password_confirmation', 'code', 'otp', 'token',
        'access_token', 'authorization', 'secret', 'api_key',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return $next($request);
        }

        $startedAt = hrtime(true);
        $status = 500;

        try {
            $response = $next($request);
            $status = $response->getStatusCode();

            return $response;
        } catch (Throwable $exception) {
            $status = $exception instanceof HttpExceptionInterface ? $exception->getStatusCode() : 500;
            throw $exception;
        } finally {
            $this->store($request, $status, $startedAt);
        }
    }

    private function store(Request $request, int $status, int $startedAt): void
    {
        try {
            [$subjectType, $subjectId] = $this->subject($request);

            DB::table('admin_audit_logs')->insert([
                'user_id' => $request->user()?->getAuthIdentifier(),
                'method' => $request->method(),
                'route_name' => $request->route()?->getName(),
                'path' => '/'.$request->path(),
                'action' => $request->route()?->getActionName(),
                'subject_type' => $subjectType,
                'subject_id' => $subjectId,
                'request_payload' => json_encode($this->sanitize($request->all()), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'response_status' => $status,
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
                'duration_ms' => max(0, (int) round((hrtime(true) - $startedAt) / 1_000_000)),
                'created_at' => now(),
            ]);
        } catch (Throwable $exception) {
            Log::warning('Admin activity could not be audited.', ['message' => $exception->getMessage()]);
        }
    }

    private function sanitize(mixed $value, ?string $key = null, int $depth = 0): mixed
    {
        if ($key !== null && in_array(strtolower($key), self::SENSITIVE_KEYS, true)) {
            return '[REDACTED]';
        }

        if ($depth > 6) {
            return '[MAX_DEPTH]';
        }

        if ($value instanceof UploadedFile) {
            return ['file' => $value->getClientOriginalName(), 'size' => $value->getSize()];
        }

        if (is_array($value)) {
            return collect($value)->mapWithKeys(fn ($item, $itemKey) => [
                $itemKey => $this->sanitize($item, is_string($itemKey) ? $itemKey : null, $depth + 1),
            ])->all();
        }

        if (is_string($value) && mb_strlen($value) > 2000) {
            return mb_substr($value, 0, 2000).'…';
        }

        return $value;
    }

    private function subject(Request $request): array
    {
        foreach (array_reverse($request->route()?->parameters() ?? [], true) as $name => $value) {
            if ($value instanceof Model) {
                return [class_basename($value), (string) $value->getKey()];
            }

            if (is_scalar($value)) {
                return [(string) $name, (string) $value];
            }
        }

        return [null, null];
    }
}
