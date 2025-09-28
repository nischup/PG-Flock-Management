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
        return Inertia::render('report/egg-receive-and-grading-report');
    }
}
