<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFlockRequest;
use App\Models\Master\Flock;
use Illuminate\Http\Request;

class FlockController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreFlockRequest $request)
    {
        $flock = Flock::create([
            'name' => $request->name,
            'parent_flock_id' => $request->parent_flock_id ?? null,
            'status' => 1,
        ]);

        // Auto-generate code based on ID
        $flock->code = 'FLOCK-'.str_pad($flock->id, 4, '0', STR_PAD_LEFT);
        $flock->save();

        return redirect()->back()->with('flock', $flock);
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
