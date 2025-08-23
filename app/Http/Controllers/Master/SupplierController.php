<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SupplierController extends Controller
{
    public function index()
    {
        $suppliers = Supplier::orderBy('id', 'desc')->get()->map(function ($supplier) {
            return [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'supplier_type' => $supplier->supplier_type,
                'address' => $supplier->address,
                'origin' => $supplier->origin,
                'contact_person' => $supplier->contact_person,
                'contact_person_email' => $supplier->contact_person_email,
                'contact_person_mobile' => $supplier->contact_person_mobile,
                'status' => $supplier->status,
                'created_at' => $supplier->created_at->format('d M Y'),
            ];
        });

        return Inertia::render('library/supplier/List', [
            'suppliers' => $suppliers,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:200',
            'supplier_type'         => 'required',
            'address'               => 'nullable|string|max:500',
            'origin'                => 'nullable|string|max:255',
            'contact_person'        => 'nullable|string|max:255',
            'contact_person_email'  => 'nullable|email|max:255',
            'contact_person_mobile' => 'nullable|string|max:20',
            'status'                => 'required|in:0,1',
        ]);

        Supplier::create($validated);

        return redirect()->route('supplier.index')->with('success', 'Supplier created successfully.');
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:200',
            'supplier_type'         => 'required',
            'address'               => 'nullable|string|max:500',
            'origin'                => 'nullable|string|max:255',
            'contact_person'        => 'nullable|string|max:255',
            'contact_person_email'  => 'nullable|email|max:255',
            'contact_person_mobile' => 'nullable|string|max:20',
            'status'                => 'required|in:0,1',
        ]);

        $supplier->update($validated);

        return redirect()->route('supplier.index')->with('success', 'Supplier updated successfully.');
    }

    public function destroy(Supplier $supplier)
    {
        $supplier->delete();
        return redirect()->route('supplier.index')->with('success', 'Supplier deleted successfully.');
    }
}
