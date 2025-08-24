<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Company;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $companies = Company::orderBy('id', 'desc')->get()->map(function ($company) {
                return [
                    'id'         => $company->id,
                    'name'       => $company->name,
                    'status'     => (int) $company->status, // keep as int for Vue
                    'location'   => $company->location,
                    'created_at' => $company->created_at->format('d M Y'),
                ];
            });

            return Inertia::render('library/company/List', [
                'companies' => $companies,
            ]);
        } catch (\Exception $e) {
            Log::error('Company index error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch companies.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:200',
            'status'   => 'nullable|integer',
            'location' => 'nullable|string|max:200',
        ]);

        try {
            Company::create([
                'name'     => $request->name,
                'status'   => $request->status ?? 1,
                'location' => $request->location,
            ]);

            return redirect()->route('company.index')->with('success', 'Company created successfully.');
        } catch (\Exception $e) {
            Log::error('Company store error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to create company.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $company = Company::findOrFail($id);

            $companyData = [
                'id'       => $company->id,
                'name'     => $company->name,
                'status'   => (int) $company->status,
                'location' => $company->location,
            ];

            return Inertia::render('library/company/Edit', [
                'company' => $companyData,
            ]);
        } catch (\Exception $e) {
            Log::error('Company edit error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch company.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'     => 'required|string|max:200',
            'status'   => 'nullable|integer',
            'location' => 'nullable|string|max:200',
        ]);

        try {
            $company = Company::findOrFail($id);
            $company->update([
                'name'     => $request->name,
                'status'   => $request->status ?? 1,
                'location' => $request->location,
            ]);

            return redirect()->route('company.index')->with('success', 'Company updated successfully.');
        } catch (\Exception $e) {
            Log::error('Company update error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to update company.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->delete();

            return redirect()->route('company.index')->with('success', 'Company deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Company delete error: '.$e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete company.');
        }
    }
}
