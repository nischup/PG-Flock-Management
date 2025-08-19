<?php

namespace App\Http\Controllers\Ps;

use App\Http\Controllers\Controller;
use App\Models\Ps\PsReceive;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PsLabTestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         $psReceives = PsReceive::query()
            ->when($request->search, fn($q) => 
                $q->where('pi_no', 'like', "%{$request->search}%")
                  ->orWhere('order_no', 'like', "%{$request->search}%")
            )
            ->paginate($request->per_page ?? 10)
            ->withQueryString();

        return Inertia::render('ps/ps-lab-test/List', [
            'psReceives' => $psReceives,
            'filters' => $request->only(['search', 'per_page']),
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
