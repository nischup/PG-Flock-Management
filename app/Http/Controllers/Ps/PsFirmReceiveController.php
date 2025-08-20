<?php

namespace App\Http\Controllers\Ps;

use App\Http\Controllers\Controller;
use App\Models\Ps\PsReceive;
use App\Models\Master\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PsFirmReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch all PS Receives (you may filter by status if needed)
        $psReceives = PsReceive::select('id', 'pi_no')->get();

        // Fetch all companies
        $companies = Company::select('id', 'name')->get();

        return Inertia::render('ps/ps-firm-receive/Create', [
            'psReceives' => $psReceives,
            'companies' => $companies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
