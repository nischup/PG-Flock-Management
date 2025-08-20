<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Company;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $companies = Company::orderBy('id', 'desc')->get();

        return Inertia::render('library/company/List', [
            'companies' => $companies,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:200',
            'status'   => 'nullable|integer',
            'location' => 'nullable|string|max:200', // ✅ match DB length
        ]);

        Company::create([
            'name'     => $request->name,
            'status'   => $request->status ?? 1,
            'location' => $request->location, // ✅ save correctly
        ]);

        return redirect()->route('company.index')->with('success', 'Company created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $company = Company::findOrFail($id);

        return Inertia::render('library/company/Edit', [
            'company' => $company,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
       $request->validate([
            'name'     => 'required|string|max:200',
            'status'   => 'nullable|integer',
            'location' => 'nullable|string|max:200', // ✅ match DB length
        ]);

        $company = Company::findOrFail($id);
        $company->update([
            'name'     => $request->name,
            'status'   => $request->status ?? 1,
            'location' => $request->location,
        ]);

        return redirect()->route('company.index')->with('success', 'Company updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $company = Company::findOrFail($id);
        $company->delete();

        return redirect()->route('company.index')->with('success', 'Company deleted successfully.');
    }
}
