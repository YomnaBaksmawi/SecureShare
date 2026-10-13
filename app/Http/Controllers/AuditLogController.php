<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuditLogIndexRequest;
use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index(AuditLogIndexRequest $request)
    {
        $logs = AuditLog::query()
            ->with(['user:id,name,email'])
            ->with([
                'user' => fn ($query) => $query->withTrashed(),
            ])
            ->when(
                $request->action,
                fn ($query, $action) => $query->where('action', $action)
            )
            ->when(
                $request->user_id,
                fn ($query, $userId) => $query->where('user_id', $userId)
            )
            ->when(
                $request->auditable_type,
                fn ($query, $type) => $query->where('auditable_type', $type)
            )
            ->when(
                $request->auditable_id,
                fn ($query, $id) => $query->where('auditable_id', $id)
            )
            ->when(
                $request->from,
                fn ($query, $from) => $query->where('created_at', '>=', $from)
            )
            ->when(
                $request->to,
                fn ($query, $to) => $query->where('created_at', '<=', $to)
            )
            ->latest('created_at')
            ->paginate($request->integer('per_page', 20));

        return response()->json([
            'success' => true,
            'message' => 'Audit logs retrieved successfully.',
            'data' => AuditLogResource::collection($logs),
            'meta' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}
