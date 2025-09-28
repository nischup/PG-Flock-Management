<?php

namespace App\Http\Controllers\Report;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

class EggReceiveAndGradingReportController extends Controller
{
    /**
     * Display the Egg Receive & Grading Report page.
     */
    public function index()
    {
        // Sample batches and totals data (this should come from your database or any data source)
        $batches = [
            [
                'flock_no' => '4',
                'breed' => 'MC',
                'age' => '77.4',
                'received_date' => '23.05.2025',
                'eggs' => '1800',
                'commercial_rejects' => '0',
                'broken' => '4',
                'liquid' => '2',
                'damage' => '6',
                'total' => '12',
                'percent' => '0.67',
                'hatchable_eggs' => '1788',
                'set_eggs' => '1788',
                'set_date' => '28.05.2025',
            ],
            // Add other batches similarly...
        ];

        $totals = [
            'eggs' => 30000,
            'broken' => 0.23,
            'liquid' => 0.10,
            'damage' => 0.48,
            'total' => 99.52,
        ];

        return Inertia::render('report/egg-receive-and-grading-report', [
            'batches' => $batches,
            'totals' => $totals,
        ]);
    }
}
