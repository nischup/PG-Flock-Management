<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Company;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;

class CompanyController extends Controller
{
    // ------------------- LIST COMPANIES -------------------
    public function index(Request $request)
    {
        try {
            $companies = Company::orderBy('id', 'desc')->get()->map(function ($company) {
                return [
                    'id'                        => $company->id,
                    'name'                      => $company->name,
                    'company_type'              => $company->company_type,
                    'location'                  => $company->location,
                    'contact_person_name'       => $company->contact_person_name,
                    'contact_person_phone'      => $company->contact_person_phone,
                    'contact_person_email'      => $company->contact_person_email,
                    'contact_person_designation'=> $company->contact_person_designation,
                    'status'                    => (int) $company->status,
                    'created_at'                => $company->created_at->format('d M Y'),
                ];
            });

            return Inertia::render('library/company/List', [
                'companies' => $companies,
            ]);
        } catch (\Exception $e) {
            Log::error('Company index error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to fetch companies.');
        }
    }

    // ------------------- CREATE COMPANY -------------------
    public function store(Request $request)
    {
        $request->validate([
            'name'                      => 'required|string|max:200',
            'company_type'              => 'nullable|string|max:100',
            'location'                  => 'nullable|string|max:200',
            'contact_person_name'       => 'nullable|string|max:150',
            'contact_person_phone'      => 'nullable|string|max:50',
            'contact_person_email'      => 'nullable|string|email|max:100',
            'contact_person_designation'=> 'nullable|string|max:100',
            'status'                    => 'nullable|integer',
        ]);

        try {
            Company::create($request->all());
            return redirect()->route('company.index')->with('success', 'Company created successfully.');
        } catch (\Exception $e) {
            Log::error('Company store error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create company.');
        }
    }

    // ------------------- UPDATE COMPANY -------------------
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name'                      => 'required|string|max:200',
            'company_type'              => 'nullable|string|max:100',
            'location'                  => 'nullable|string|max:200',
            'contact_person_name'       => 'nullable|string|max:150',
            'contact_person_phone'      => 'nullable|string|max:50',
            'contact_person_email'      => 'nullable|string|email|max:100',
            'contact_person_designation'=> 'nullable|string|max:100',
            'status'                    => 'nullable|integer',
        ]);

        try {
            $company = Company::findOrFail($id);
            $company->update($request->all());

            return redirect()->route('company.index')->with('success', 'Company updated successfully.');
        } catch (\Exception $e) {
            Log::error('Company update error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to update company.');
        }
    }

    // ------------------- DELETE COMPANY -------------------
    public function destroy(string $id)
    {
        try {
            $company = Company::findOrFail($id);
            $company->delete();

            return redirect()->route('company.index')->with('success', 'Company deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Company delete error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to delete company.');
        }
    }

    // ------------------- PDF EXPORT -------------------
    public function downloadPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $companies = Company::orderBy('id', 'desc')
            ->get()
            ->map(fn($c) => [
                'name' => $c->name,
                'company_type' => $c->company_type,
                'location' => $c->location,
                'contact_person_name' => $c->contact_person_name,
                'contact_person_phone' => $c->contact_person_phone,
                'contact_person_email' => $c->contact_person_email,
                'contact_person_designation' => $c->contact_person_designation,
                'status' => $c->status,
                'created_at' => $c->created_at ? $c->created_at->format('Y-m-d H:i') : '',
            ])->toArray();

        $columns = [
            ['label'=>'#', 'key'=>'index', 'callback'=>fn($r,$i)=>$i+1],
            ['label'=>'Name', 'key'=>'name'],
            ['label'=>'Type', 'key'=>'company_type'],
            ['label'=>'Location', 'key'=>'location'],
            ['label'=>'Contact Name', 'key'=>'contact_person_name'],
            ['label'=>'Contact Phone', 'key'=>'contact_person_phone'],
            ['label'=>'Contact Email', 'key'=>'contact_person_email'],
            ['label'=>'Designation', 'key'=>'contact_person_designation'],
            ['label'=>'Status', 'key'=>'status', 'callback'=>fn($r)=> $r['status'] ? 'Active' : 'Inactive'],
            ['label'=>'Created', 'key'=>'created_at'],
        ];

        $data = [
            'title' => 'Company List',
            'columns' => $columns,
            'rows' => $companies,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled'=>true, 'isRemoteEnabled'=>true, 'defaultFont'=>'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
                  ->setPaper('a4', $data['orientation']);

        return $pdf->stream('company-list.pdf');
    }

    // ------------------- EXCEL EXPORT -------------------
    public function downloadExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $rows = Company::orderBy('id', 'desc')
            ->get()
            ->map(fn($c) => [
                'name' => $c->name,
                'company_type' => $c->company_type,
                'location' => $c->location,
                'contact_person_name' => $c->contact_person_name,
                'contact_person_phone' => $c->contact_person_phone,
                'contact_person_email' => $c->contact_person_email,
                'contact_person_designation' => $c->contact_person_designation,
                'status' => $c->status,
                'created_at' => $c->created_at ? $c->created_at->format('Y-m-d H:i') : '',
            ])->toArray();

        $columns = [
            ['label'=>'#', 'key'=>'index', 'callback'=>fn($r,$i)=> $i+1],
            ['label'=>'Name', 'key'=>'name'],
            ['label'=>'Type', 'key'=>'company_type'],
            ['label'=>'Location', 'key'=>'location'],
            ['label'=>'Contact Name', 'key'=>'contact_person_name'],
            ['label'=>'Contact Phone', 'key'=>'contact_person_phone'],
            ['label'=>'Contact Email', 'key'=>'contact_person_email'],
            ['label'=>'Designation', 'key'=>'contact_person_designation'],
            ['label'=>'Status', 'key'=>'status', 'callback'=>fn($r)=> $r['status'] ? 'Active' : 'Inactive'],
            ['label'=>'Created', 'key'=>'created_at'],
        ];

        $headings = array_map(fn($c)=>$c['label'], $columns);

        $body = [];
        foreach($rows as $i => $row){
            $line = [];
            foreach($columns as $col){
                $val = $col['callback'] ?? null;
                $line[] = $val && is_callable($val) ? $val($row,$i) : ($row[$col['key']] ?? '');
            }
            $body[] = $line;
        }

        return Excel::download(new ArrayExport($headings, $body), 'companies-report.xlsx');
    }
}
