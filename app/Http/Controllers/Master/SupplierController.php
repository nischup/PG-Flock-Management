<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Supplier;
use Illuminate\Support\Facades\Log;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         try {
        $suppliers = Supplier::orderBy('id', 'desc')
            ->get()
            ->map(function ($supplier) {
                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'address' => $supplier->address,
                    'origin' => $supplier->origin,
                    'contact_person' => $supplier->contact_person,
                    'contact_person_email' => $supplier->contact_person_email,
                    'contact_person_mobile' => $supplier->contact_person_mobile,
                    'status' => $supplier->status,
                    // Format created_at without microseconds
                    'created_at' => $supplier->created_at ? $supplier->created_at->format('Y-m-d') : null,
                    'updated_at' => $supplier->updated_at ? $supplier->updated_at->format('Y-m-d') : null,
                ];
            });

        return Inertia::render('library/supplier/List', [
            'suppliers' => $suppliers,
        ]);
    } catch (\Exception $e) {
            Log::error('Supplier Index Error: ' . $e->getMessage());
            return Inertia::render('library/supplier/List', [
                'suppliers' => [],
                'error' => 'Failed to load suppliers.'
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'supplier.name'                  => 'required|string|max:255',
                'supplier.address'               => 'nullable|string|max:500',
                'supplier.origin'                => 'nullable|string|max:255',
                'supplier.contact_person'        => 'nullable|string|max:255',
                'supplier.contact_person_email'  => 'nullable|email|max:255',
                'supplier.contact_person_mobile' => 'nullable|string|max:20',
                'supplier.status'                => 'required|in:0,1',
            ]);

            Supplier::create($validated['supplier']);

            return redirect()->route('supplier.index')
                ->with('success', 'Supplier created successfully.');
        } catch (\Exception $e) {
            Log::error('Supplier Store Error: ' . $e->getMessage());
            return redirect()->route('supplier.index')
                ->with('error', 'Failed to create supplier.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            return Inertia::render('library/supplier/Edit', [
                'supplier' => $supplier,
            ]);
        } catch (\Exception $e) {
            Log::error('Supplier Edit Error: ' . $e->getMessage());
            return redirect()->route('supplier.index')
                ->with('error', 'Supplier not found.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

            $validated = $request->validate([
                'supplier.name'                  => 'required|string|max:255',
                'supplier.address'               => 'nullable|string|max:500',
                'supplier.origin'                => 'nullable|string|max:255',
                'supplier.contact_person'        => 'nullable|string|max:255',
                'supplier.contact_person_email'  => 'nullable|email|max:255',
                'supplier.contact_person_mobile' => 'nullable|string|max:20',
                'supplier.status'                => 'required|in:0,1',
            ]);

            $supplier->update($validated['supplier']);

            return redirect()->route('supplier.index')
                ->with('success', 'Supplier updated successfully.');
        } catch (\Exception $e) {
            Log::error('Supplier Update Error: ' . $e->getMessage());
            return redirect()->route('supplier.index')
                ->with('error', 'Failed to update supplier.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $supplier = Supplier::findOrFail($id);
            $supplier->delete();

            return redirect()->route('supplier.index')
                ->with('success', 'Supplier deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Supplier Delete Error: ' . $e->getMessage());
            return redirect()->route('supplier.index')
                ->with('error', 'Failed to delete supplier.');
        }
    }
}
