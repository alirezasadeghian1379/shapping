<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'method' => ['nullable', 'in:POST,PUT,PATCH,DELETE'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:100'],
        ]);

        return DB::table('admin_audit_logs as logs')
            ->leftJoin('users', 'users.id', '=', 'logs.user_id')
            ->select('logs.*', 'users.name as user_name', 'users.mobile as user_mobile')
            ->when($filters['method'] ?? null, fn ($query, $method) => $query->where('logs.method', $method))
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('logs.user_id', $userId))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('logs.created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('logs.created_at', '<=', $to))
            ->when($filters['search'] ?? null, function ($query, $search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('logs.path', 'like', "%{$search}%")
                        ->orWhere('logs.action', 'like', "%{$search}%")
                        ->orWhere('logs.ip', 'like', "%{$search}%")
                        ->orWhere('users.name', 'like', "%{$search}%")
                        ->orWhere('users.mobile', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('logs.id')
            ->paginate($filters['per_page'] ?? 25);
    }
}
