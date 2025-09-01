<?php

namespace App\Http\Controllers\Shed;

use App\Http\Controllers\Controller;
use App\Models\Shed\ShedReceive;
use App\Models\Master\Company;
use App\Models\Ps\PsReceive;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BatchAssignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('shed/batch-assign/List');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('shed/batch-assign/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
