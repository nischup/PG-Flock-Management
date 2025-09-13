<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuditLogService
{
    public static function log(
        string $event,
        Model $model,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $tags = null,
        ?Request $request = null
    ): void {
        $request = $request ?? request();

        AuditLog::create([
            'event' => $event,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'tags' => $tags,
            'user_id' => Auth::id(),
        ]);
    }

    public static function logCreated(Model $model, ?Request $request = null): void
    {
        self::log('created', $model, null, $model->getAttributes(), null, $request);
    }

    public static function logUpdated(Model $model, array $oldValues, ?Request $request = null): void
    {
        self::log('updated', $model, $oldValues, $model->getChanges(), null, $request);
    }

    public static function logDeleted(Model $model, ?Request $request = null): void
    {
        self::log('deleted', $model, $model->getAttributes(), null, null, $request);
    }

    public static function logViewed(Model $model, ?Request $request = null): void
    {
        self::log('viewed', $model, null, null, null, $request);
    }

    public static function logLogin(?Request $request = null): void
    {
        $request = $request ?? request();

        AuditLog::create([
            'event' => 'login',
            'auditable_type' => 'User',
            'auditable_id' => Auth::id(),
            'old_values' => null,
            'new_values' => ['user_id' => Auth::id()],
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'tags' => 'authentication',
            'user_id' => Auth::id(),
        ]);
    }

    public static function logLogout(?Request $request = null): void
    {
        $request = $request ?? request();

        AuditLog::create([
            'event' => 'logout',
            'auditable_type' => 'User',
            'auditable_id' => Auth::id(),
            'old_values' => ['user_id' => Auth::id()],
            'new_values' => null,
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'tags' => 'authentication',
            'user_id' => Auth::id(),
        ]);
    }

    public static function logCustom(
        string $event,
        string $modelType,
        int $modelId,
        ?array $data = null,
        ?string $tags = null,
        ?Request $request = null
    ): void {
        $request = $request ?? request();

        AuditLog::create([
            'event' => $event,
            'auditable_type' => $modelType,
            'auditable_id' => $modelId,
            'old_values' => null,
            'new_values' => $data,
            'url' => $request->fullUrl(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'tags' => $tags,
            'user_id' => Auth::id(),
        ]);
    }

    public static function getActivitySummary(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_activities' => AuditLog::where('created_at', '>=', $startDate)->count(),
            'unique_users' => AuditLog::where('created_at', '>=', $startDate)
                ->distinct('user_id')
                ->count('user_id'),
            'most_active_event' => AuditLog::where('created_at', '>=', $startDate)
                ->selectRaw('event, COUNT(*) as count')
                ->groupBy('event')
                ->orderBy('count', 'desc')
                ->first(),
            'most_active_model' => AuditLog::where('created_at', '>=', $startDate)
                ->selectRaw('auditable_type, COUNT(*) as count')
                ->groupBy('auditable_type')
                ->orderBy('count', 'desc')
                ->first(),
        ];
    }
}
