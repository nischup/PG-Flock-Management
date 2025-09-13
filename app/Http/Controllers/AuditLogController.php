<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = AuditLog::with(['user', 'auditable'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('event')) {
            $query->byEvent($request->event);
        }

        if ($request->filled('user_id')) {
            $query->byUser($request->user_id);
        }

        if ($request->filled('model_type')) {
            $query->byModel($request->model_type);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->byDateRange($request->date_from, $request->date_to);
        }

        $auditLogs = $query->paginate(50);

        // Get analysis data
        $analysis = $this->getAnalysisData($request);

        // Get filter options
        $events = AuditLog::distinct()->pluck('event')->sort()->values();
        $modelTypes = AuditLog::distinct()->pluck('auditable_type')->sort()->values();
        $users = User::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('AuditLog/Index', [
            'auditLogs' => $auditLogs,
            'analysis' => $analysis,
            'filters' => [
                'events' => $events,
                'modelTypes' => $modelTypes,
                'users' => $users,
                'current' => $request->only(['event', 'user_id', 'model_type', 'date_from', 'date_to']),
            ],
        ]);
    }

    private function getAnalysisData(Request $request): array
    {
        $baseQuery = AuditLog::query();

        // Apply same filters as main query
        if ($request->filled('event')) {
            $baseQuery->byEvent($request->event);
        }

        if ($request->filled('user_id')) {
            $baseQuery->byUser($request->user_id);
        }

        if ($request->filled('model_type')) {
            $baseQuery->byModel($request->model_type);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $baseQuery->byDateRange($request->date_from, $request->date_to);
        }

        // Event distribution
        $eventDistribution = $baseQuery->clone()
            ->selectRaw('event, COUNT(*) as count')
            ->groupBy('event')
            ->orderBy('count', 'desc')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->event => $item->count]);

        // Model type distribution
        $modelDistribution = $baseQuery->clone()
            ->selectRaw('auditable_type, COUNT(*) as count')
            ->groupBy('auditable_type')
            ->orderBy('count', 'desc')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->auditable_type => $item->count]);

        // User activity
        $userActivity = $baseQuery->clone()
            ->with('user')
            ->selectRaw('user_id, COUNT(*) as count')
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get()
            ->map(fn ($item) => [
                'user' => $item->user?->name ?? 'Unknown',
                'count' => $item->count,
            ]);

        // Daily activity (last 30 days)
        $dailyActivity = $baseQuery->clone()
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => $item->date,
                'count' => $item->count,
            ]);

        // Hourly activity (last 24 hours)
        $hourlyActivity = $baseQuery->clone()
            ->where('created_at', '>=', Carbon::now()->subDay())
            ->get()
            ->groupBy(fn ($item) => $item->created_at->hour)
            ->map(fn ($items, $hour) => [
                'hour' => (int) $hour,
                'count' => $items->count(),
            ])
            ->sortBy('hour')
            ->values();

        // Recent activity summary
        $recentActivity = $baseQuery->clone()
            ->with(['user', 'auditable'])
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($log) => [
                'id' => $log->id,
                'event' => $log->event,
                'model' => class_basename($log->auditable_type),
                'user' => $log->user?->name ?? 'System',
                'created_at' => $log->created_at->format('Y-m-d H:i:s'),
                'url' => $log->url,
            ]);

        // Get date range from a fresh query
        $dateRangeQuery = AuditLog::query();

        // Apply same filters as main query
        if ($request->filled('event')) {
            $dateRangeQuery->byEvent($request->event);
        }
        if ($request->filled('user_id')) {
            $dateRangeQuery->byUser($request->user_id);
        }
        if ($request->filled('model_type')) {
            $dateRangeQuery->byModel($request->model_type);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $dateRangeQuery->byDateRange($request->date_from, $request->date_to);
        }

        $minDate = $dateRangeQuery->min('created_at');
        $maxDate = $dateRangeQuery->max('created_at');

        return [
            'eventDistribution' => $eventDistribution,
            'modelDistribution' => $modelDistribution,
            'userActivity' => $userActivity,
            'dailyActivity' => $dailyActivity,
            'hourlyActivity' => $hourlyActivity,
            'recentActivity' => $recentActivity,
            'totalLogs' => $baseQuery->count(),
            'uniqueUsers' => $baseQuery->distinct('user_id')->count('user_id'),
            'dateRange' => [
                'from' => $minDate ? Carbon::parse($minDate)->format('Y-m-d') : null,
                'to' => $maxDate ? Carbon::parse($maxDate)->format('Y-m-d') : null,
            ],
        ];
    }
}
