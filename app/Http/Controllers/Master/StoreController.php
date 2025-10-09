<?php

namespace App\Http\Controllers\Master;
use App\Http\Controllers\Controller;
use App\Models\Master\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StoreController extends Controller
{
    /**
     * Display a listing of stores.
     */
    public function index()
    {
        $stores = Store::orderBy('id', 'desc')->get();
        // return Inertia::render('Stores/Index', [
        //     'stores' => $stores
        // ]);
    }

    /**
     * Show form to create a new store.
     */
    public function create()
    {
        // return Inertia::render('Stores/Create');
    }

    /**
     * Store a new store in database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'code' => 'nullable|string|unique:stores,code',
        ]);

        Store::create($request->all());

        // return redirect()->route('stores.index')
        //     ->with('success', 'Store created successfully');
    }

    /**
     * Show form to edit a store.
     */
    public function edit(Store $store)
    {
        // return Inertia::render('Stores/Edit', [
        //     'store' => $store
        // ]);
    }

    /**
     * Update a store in database.
     */
    public function update(Request $request, Store $store)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'code' => 'nullable|string|unique:stores,code,' . $store->id,
        ]);

        $store->update($request->all());

        // return redirect()->route('stores.index')
        //     ->with('success', 'Store updated successfully');
    }

    /**
     * Delete a store.
     */
    public function destroy(Store $store)
    {
        $store->delete();

        // return redirect()->route('stores.index')
        //     ->with('success', 'Store deleted successfully');
    }
}
