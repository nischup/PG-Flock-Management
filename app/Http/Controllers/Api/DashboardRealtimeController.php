<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardRealtimeService;
use App\Events\DashboardDataUpdated;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardRealtimeController extends Controller
{
    protected $realtimeService;

    public function __construct(DashboardRealtimeService $realtimeService)
    {
        $this->realtimeService = $realtimeService;
    }

    /**
     * Get real-time dashboard data
     */
    public function getRealtimeData(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $data = $this->realtimeService->getRealtimeData($filters);

            return response()->json($data);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch real-time data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Trigger real-time data update
     */
    public function triggerUpdate(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $data = $this->realtimeService->getRealtimeData($filters);

            // Broadcast the update
            broadcast(new DashboardDataUpdated($data, $filters));

            return response()->json([
                'success' => true,
                'message' => 'Real-time update triggered',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to trigger update',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get batch performance data for table
     */
    public function getBatchPerformanceData(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $data = $this->realtimeService->getBatchPerformanceData($filters);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch batch performance data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed flock information for modal
     */
    public function getFlockDetails(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $data = $this->realtimeService->getFlockDetails($filters);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch flock details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed birds information by batch for modal
     */
    public function getBirdsDetails(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $data = $this->realtimeService->getBirdsDetails($filters);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch birds details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed mortality information for modal
     */
    public function getMortalityDetails(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $data = $this->realtimeService->getMortalityDetails($filters);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch mortality details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed daily eggs information for modal
     */
    public function getDailyEggsDetails(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $data = $this->realtimeService->getDailyEggsDetails($filters);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch daily eggs details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed hatchable eggs information for modal
     */
    public function getHatchableEggsDetails(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $data = $this->realtimeService->getHatchableEggsDetails($filters);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch hatchable eggs details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed male birds information for modal
     */
    public function getMaleBirdsDetails(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $data = $this->realtimeService->getMaleBirdsDetails($filters);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch male birds details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed female birds information for modal
     */
    public function getFemaleBirdsDetails(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $data = $this->realtimeService->getFemaleBirdsDetails($filters);

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch female birds details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dashboard data with polling
     */
    public function pollData(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'company', 'project', 'flock', 'shed', 'batch', 
                'date', 'date_from', 'date_to'
            ]);

            $lastUpdate = $request->input('last_update', 0);
            $data = $this->realtimeService->getRealtimeData($filters);

            // Check if data has changed
            $hasChanged = $data['timestamp'] > $lastUpdate;

            return response()->json([
                'success' => true,
                'data' => $data,
                'hasChanged' => $hasChanged,
                'timestamp' => $data['timestamp']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to poll data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
