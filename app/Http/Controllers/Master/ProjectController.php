<?php

namespace App\Http\Controllers\Master;

use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;
use Illuminate\Http\Request;
use App\Models\Master\Company;
use App\Models\Master\Project;
use App\Http\Controllers\Controller;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with('company')->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhereHas('company', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $projects = $query->get();
        $companies = Company::orderBy('name')->get();

        $filters = $request->only(['search', 'per_page', 'page']);

        return Inertia::render('library/project/List', [
            'projects'  => $projects,
            'companies' => $companies,
            'filters'   => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:200',
            'project_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:200',
            'contact_person_name' => 'nullable|string|max:150',
            'contact_person_phone' => 'nullable|string|max:50',
            'contact_person_email' => 'nullable|string|max:100',
            'contact_person_designation' => 'nullable|string|max:100',
            'status' => 'nullable|boolean',
        ]);

        Project::create($validated);

        return redirect()->back()->with('success', 'Project created successfully.');
    }

    public function update(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:200',
            'project_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:200',
            'contact_person_name' => 'nullable|string|max:150',
            'contact_person_phone' => 'nullable|string|max:50',
            'contact_person_email' => 'nullable|string|max:100',
            'contact_person_designation' => 'nullable|string|max:100',
            'status' => 'nullable|boolean',
        ]);

        $project->update($validated);

        return redirect()->back()->with('success', 'Project updated successfully.');
    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return redirect()->back()->with('success', 'Project deleted successfully.');
    }

    public function show($id)
    {
        $project = Project::with('company')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $project
        ]);
    }

    // -----------------------------
    // PDF Report
    // -----------------------------
    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $projects = Project::with('company')
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('company', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            })
            ->orderBy($request->get('sort', 'id'), $request->get('direction', 'desc'))
            ->get()
            ->map(fn($p) => [
                'company' => $p->company?->name,
                'name' => $p->name,
                'type' => $p->project_type,
                'location' => $p->location,
                'contact_name' => $p->contact_person_name,
                'phone' => $p->contact_person_phone,
                'email' => $p->contact_person_email,
                'designation' => $p->contact_person_designation,
                'status' => $p->status,
                'created_at' => $p->created_at?->format('Y-m-d H:i'),
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Company', 'key' => 'company'],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Type', 'key' => 'type'],
            ['label' => 'Location', 'key' => 'location'],
            ['label' => 'Contact Name', 'key' => 'contact_name'],
            ['label' => 'Phone', 'key' => 'phone'],
            ['label' => 'Email', 'key' => 'email'],
            ['label' => 'Designation', 'key' => 'designation'],
            ['label' => 'Status', 'key' => 'status', 'callback' => fn($r) => $r['status'] ? 'Active' : 'Inactive'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        $data = [
            'title' => 'Project List',
            'columns' => $columns,
            'rows' => $projects,
            'filters' => $request->all(),
            'generatedAt' => now(),
            'orientation' => $request->get('orientation', 'portrait'),
        ];

        Pdf::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'defaultFont' => 'DejaVu Sans']);
        $pdf = Pdf::loadView('reports.common.list', $data)
                  ->setPaper('a4', $data['orientation']);

        return $pdf->stream('projects-report.pdf');
    }

    // -----------------------------
    // Excel Report
    // -----------------------------
    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        $projects = Project::with('company')
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('company', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            })
            ->orderBy($request->get('sort', 'id'), $request->get('direction', 'desc'))
            ->get()
            ->map(fn($p) => [
                'company' => $p->company?->name,
                'name' => $p->name,
                'type' => $p->project_type,
                'location' => $p->location,
                'contact_name' => $p->contact_person_name,
                'phone' => $p->contact_person_phone,
                'email' => $p->contact_person_email,
                'designation' => $p->contact_person_designation,
                'status' => $p->status,
                'created_at' => $p->created_at?->format('Y-m-d H:i'),
            ])->toArray();

        $columns = [
            ['label' => '#', 'key' => 'index', 'callback' => fn($r, $i) => $i + 1],
            ['label' => 'Company', 'key' => 'company'],
            ['label' => 'Name', 'key' => 'name'],
            ['label' => 'Type', 'key' => 'type'],
            ['label' => 'Location', 'key' => 'location'],
            ['label' => 'Contact Name', 'key' => 'contact_name'],
            ['label' => 'Phone', 'key' => 'phone'],
            ['label' => 'Email', 'key' => 'email'],
            ['label' => 'Designation', 'key' => 'designation'],
            ['label' => 'Status', 'key' => 'status', 'callback' => fn($r) => $r['status'] ? 'Active' : 'Inactive'],
            ['label' => 'Created At', 'key' => 'created_at'],
        ];

        // Build Excel headings
        $headings = array_map(fn($c) => $c['label'], $columns);

        $body = [];
        foreach ($projects as $i => $row) {
            $line = [];
            foreach ($columns as $col) {
                $val = isset($col['callback']) && is_callable($col['callback'])
                    ? $col['callback']($row, $i)
                    : ($row[$col['key']] ?? '');
                if (is_array($val)) $val = json_encode($val);
                $line[] = $val;
            }
            $body[] = $line;
        }

        return Excel::download(new ArrayExport($headings, $body), 'projects-report.xlsx');
    }
}
