<?php

namespace App\Http\Controllers\VaccineSchedule;

use App\Http\Controllers\Controller;
use App\Models\Master\BreedType;
use App\Models\Master\Company;
use App\Models\Master\Disease;
use App\Models\Master\Shed;
use App\Models\Master\Vaccine;
use App\Models\Master\VaccineType;
use App\Models\Master\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VaccineScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vaccineTypes = VaccineType::where('status', 1)
            ->orderBy('name')
            ->get();

        $companies = Company::where('status', 1)
            ->orderBy('name')
            ->get();

        $projects = Project::where('status', '1')
            ->orderBy('name')
            ->get();

        $sheds = Shed::where('status', 1)
            ->orderBy('name')
            ->get();

        $breedTypes = BreedType::where('status', 1)
            ->orderBy('name')
            ->get();

        $diseases = Disease::where('status', 1)
            ->orderBy('name')
            ->get();

        $vaccines = Vaccine::where('status', 1)
            ->orderBy('name')
            ->get();

        // Fetch vaccine schedules with their details and relationships
        $vaccineSchedules = \App\Models\VaccineSchedule::with([
            'company',
            'project',
            'shed',
            'breedType',
            'vaccineScheduleDetails.disease',
            'vaccineScheduleDetails.vaccine',
        ])->latest()->get();

        return Inertia::render('vaccine-schedule/vaccine-schedule/List', [
            'vaccineTypes' => $vaccineTypes->map(function ($vt) {
                return [
                    'id' => $vt->id,
                    'name' => $vt->name,
                ];
            })->toArray(),
            'companies' => $companies->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->name,
                ];
            })->toArray(),
            'projects' => $projects->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'code' => $p->code,
                ];
            })->toArray(),
            'sheds' => $sheds->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                ];
            })->toArray(),
            'breedTypes' => $breedTypes->map(function ($bt) {
                return [
                    'id' => $bt->id,
                    'name' => $bt->name,
                ];
            })->toArray(),
            'diseases' => $diseases->map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                ];
            })->toArray(),
            'vaccines' => $vaccines->map(function ($v) {
                return [
                    'id' => $v->id,
                    'name' => $v->name,
                ];
            })->toArray(),
            'vaccineSchedules' => $vaccineSchedules->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'company_id' => $schedule->company_id,
                    'company_name' => $schedule->company->name ?? 'N/A',
                    'job_no' => $schedule->job_no,
                    'project_id' => $schedule->project_id,
                    'project_name' => $schedule->project->name ?? 'N/A',
                    'project_code' => $schedule->project->code ?? 'N/A',
                    'flock_no' => $schedule->flock_no,
                    'shed_id' => $schedule->shed_id,
                    'shed_name' => $schedule->shed->name ?? 'N/A',
                    'batch_no' => $schedule->batch_no,
                    'breed_type_id' => $schedule->breed_type_id,
                    'breed_type_name' => $schedule->breedType->name ?? 'N/A',
                    'status' => $schedule->status,
                    'created_at' => $schedule->created_at,
                    'updated_at' => $schedule->updated_at,
                    'details' => $schedule->vaccineScheduleDetails->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'disease_id' => $detail->disease_id,
                            'disease_name' => $detail->disease->name ?? 'N/A',
                            'vaccine_id' => $detail->vaccine_id,
                            'vaccine_name' => $detail->vaccine->name ?? 'N/A',
                            'age' => $detail->age,
                            'vaccination_date' => $detail->vaccination_date,
                            'next_vaccination_date' => $detail->next_vaccination_date,
                            'status' => $detail->status,
                            'notes' => $detail->notes,
                            'administered_by' => $detail->administered_by,
                            'is_active' => $detail->is_active,
                        ];
                    })->toArray(),
                ];
            })->toArray(),
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
        try {
            $validated = $request->validate([
                'company_id' => 'required|exists:companies,id',
                'job_no' => 'required|string|max:50',
                'project_id' => 'required|exists:projects,id',
                'flock_no' => 'required|string|max:50',
                'shed_id' => 'required|exists:sheds,id',
                'batch_no' => 'required|string|max:50',
                'breed_type_id' => 'required|exists:breed_types,id',
                'stages' => 'required|array|min:1',
                'stages.*.disease_id' => 'required|exists:diseases,id',
                'stages.*.vaccine_id' => 'required|exists:vaccines,id',
                'stages.*.age' => 'required|string|max:50',
                'stages.*.vaccination_date' => 'required|date',
                'stages.*.next_vaccination_date' => 'nullable|date|after:stages.*.vaccination_date',
                'stages.*.notes' => 'nullable|string',
                'stages.*.administered_by' => 'nullable|string|max:100',
            ], [
                // Basic Information Validation Messages
                'company_id.required' => 'Please select a company.',
                'company_id.exists' => 'The selected company is invalid.',
                'job_no.required' => 'Job number is required.',
                'job_no.string' => 'Job number must be a valid text.',
                'job_no.max' => 'Job number cannot exceed 50 characters.',
                'project_id.required' => 'Please select a project.',
                'project_id.exists' => 'The selected project is invalid.',
                'flock_no.required' => 'Flock number is required.',
                'flock_no.string' => 'Flock number must be a valid text.',
                'flock_no.max' => 'Flock number cannot exceed 50 characters.',
                'shed_id.required' => 'Please select a shed.',
                'shed_id.exists' => 'The selected shed is invalid.',
                'batch_no.required' => 'Batch number is required.',
                'batch_no.string' => 'Batch number must be a valid text.',
                'batch_no.max' => 'Batch number cannot exceed 50 characters.',
                'breed_type_id.required' => 'Please select a breed type.',
                'breed_type_id.exists' => 'The selected breed type is invalid.',

                // Stages Validation Messages
                'stages.required' => 'At least one vaccination stage is required.',
                'stages.array' => 'Vaccination stages must be provided as a list.',
                'stages.min' => 'At least one vaccination stage is required.',

                // Stage Details Validation Messages
                'stages.*.disease_id.required' => 'Please select a disease for each vaccination stage.',
                'stages.*.disease_id.exists' => 'The selected disease is invalid.',
                'stages.*.vaccine_id.required' => 'Please select a vaccine for each vaccination stage.',
                'stages.*.vaccine_id.exists' => 'The selected vaccine is invalid.',
                'stages.*.age.required' => 'Age is required for each vaccination stage.',
                'stages.*.age.string' => 'Age must be a valid text.',
                'stages.*.age.max' => 'Age cannot exceed 50 characters.',
                'stages.*.vaccination_date.required' => 'Vaccination date is required for each stage.',
                'stages.*.vaccination_date.date' => 'Vaccination date must be a valid date.',
                'stages.*.next_vaccination_date.date' => 'Next vaccination date must be a valid date.',
                'stages.*.next_vaccination_date.after' => 'Next vaccination date must be after the vaccination date.',
                'stages.*.notes.string' => 'Notes must be valid text.',
                'stages.*.administered_by.string' => 'Administered by must be valid text.',
                'stages.*.administered_by.max' => 'Administered by cannot exceed 100 characters.',
            ]);

            // Create the vaccine schedule
            $vaccineSchedule = \App\Models\VaccineSchedule::create([
                'company_id' => $validated['company_id'],
                'job_no' => $validated['job_no'],
                'project_id' => $validated['project_id'],
                'flock_no' => $validated['flock_no'],
                'shed_id' => $validated['shed_id'],
                'batch_no' => $validated['batch_no'],
                'breed_type_id' => $validated['breed_type_id'],
                'status' => 1,
            ]);

            // Create vaccine schedule details for each stage
            foreach ($validated['stages'] as $stage) {
                \App\Models\VaccineScheduleDetail::create([
                    'vaccine_schedule_id' => $vaccineSchedule->id,
                    'disease_id' => $stage['disease_id'],
                    'vaccine_id' => $stage['vaccine_id'],
                    'age' => $stage['age'],
                    'vaccination_date' => $stage['vaccination_date'],
                    'next_vaccination_date' => $stage['next_vaccination_date'] ?? null,
                    'status' => 'pending',
                    'notes' => $stage['notes'] ?? null,
                    'administered_by' => $stage['administered_by'] ?? null,
                    'is_active' => 1,
                ]);
            }

            return redirect()->back()->with('success', 'Vaccine schedule created successfully.');
        } catch (\Exception $e) {
            \Log::error('Vaccine Schedule Store Error: '.$e->getMessage());

            return redirect()->back()->with('error', 'Failed to create vaccine schedule.');
        }
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
        try {
            // Validate the request
            $request->validate([
                'company_id' => 'required|exists:companies,id',
                'job_no' => 'required|string|max:255',
                'project_id' => 'required|exists:projects,id',
                'flock_no' => 'required|string|max:255',
                'shed_id' => 'required|exists:sheds,id',
                'batch_no' => 'required|string|max:255',
                'breed_type_id' => 'required|exists:breed_types,id',
                'stages' => 'required|array|min:1',
                'stages.*.disease_id' => 'required|exists:diseases,id',
                'stages.*.vaccine_id' => 'required|exists:vaccines,id',
                'stages.*.age' => 'required|string|max:255',
                'stages.*.vaccination_date' => 'required|date',
                'stages.*.next_vaccination_date' => 'nullable|date|after:stages.*.vaccination_date',
                'stages.*.notes' => 'nullable|string|max:1000',
                'stages.*.administered_by' => 'nullable|string|max:255',
            ], [
                'company_id.required' => 'Please select a company.',
                'company_id.exists' => 'The selected company is invalid.',
                'job_no.required' => 'Job number is required.',
                'job_no.string' => 'Job number must be a valid text.',
                'job_no.max' => 'Job number cannot exceed 255 characters.',
                'project_id.required' => 'Please select a project.',
                'project_id.exists' => 'The selected project is invalid.',
                'flock_no.required' => 'Flock number is required.',
                'flock_no.string' => 'Flock number must be a valid text.',
                'flock_no.max' => 'Flock number cannot exceed 255 characters.',
                'shed_id.required' => 'Please select a shed.',
                'shed_id.exists' => 'The selected shed is invalid.',
                'batch_no.required' => 'Batch number is required.',
                'batch_no.string' => 'Batch number must be a valid text.',
                'batch_no.max' => 'Batch number cannot exceed 255 characters.',
                'breed_type_id.required' => 'Please select a breed type.',
                'breed_type_id.exists' => 'The selected breed type is invalid.',
                'stages.required' => 'At least one vaccination stage is required.',
                'stages.array' => 'Vaccination stages must be provided as a list.',
                'stages.min' => 'At least one vaccination stage is required.',
                'stages.*.disease_id.required' => 'Please select a disease for each vaccination stage.',
                'stages.*.disease_id.exists' => 'The selected disease is invalid.',
                'stages.*.vaccine_id.required' => 'Please select a vaccine for each vaccination stage.',
                'stages.*.vaccine_id.exists' => 'The selected vaccine is invalid.',
                'stages.*.age.required' => 'Age is required for each vaccination stage.',
                'stages.*.age.string' => 'Age must be a valid text.',
                'stages.*.age.max' => 'Age cannot exceed 255 characters.',
                'stages.*.vaccination_date.required' => 'Vaccination date is required for each stage.',
                'stages.*.vaccination_date.date' => 'Vaccination date must be a valid date.',
                'stages.*.next_vaccination_date.date' => 'Next vaccination date must be a valid date.',
                'stages.*.next_vaccination_date.after' => 'Next vaccination date must be after the vaccination date.',
                'stages.*.notes.string' => 'Notes must be a valid text.',
                'stages.*.notes.max' => 'Notes cannot exceed 1000 characters.',
                'stages.*.administered_by.string' => 'Administered by must be a valid text.',
                'stages.*.administered_by.max' => 'Administered by cannot exceed 255 characters.',
            ]);

            // Find the vaccine schedule
            $vaccineSchedule = \App\Models\VaccineSchedule::findOrFail($id);

            // Update the main schedule
            $vaccineSchedule->update([
                'company_id' => $request->company_id,
                'job_no' => $request->job_no,
                'project_id' => $request->project_id,
                'flock_no' => $request->flock_no,
                'shed_id' => $request->shed_id,
                'batch_no' => $request->batch_no,
                'breed_type_id' => $request->breed_type_id,
            ]);

            // Delete existing details
            $vaccineSchedule->vaccineScheduleDetails()->delete();

            // Create new details
            foreach ($request->stages as $stage) {
                \App\Models\VaccineScheduleDetail::create([
                    'vaccine_schedule_id' => $vaccineSchedule->id,
                    'disease_id' => $stage['disease_id'],
                    'vaccine_id' => $stage['vaccine_id'],
                    'age' => $stage['age'],
                    'vaccination_date' => $stage['vaccination_date'],
                    'next_vaccination_date' => $stage['next_vaccination_date'] ?? null,
                    'notes' => $stage['notes'] ?? null,
                    'administered_by' => $stage['administered_by'] ?? null,
                    'status' => 'pending',
                    'is_active' => 1,
                ]);
            }

            return back()->with('success', 'Vaccine schedule updated successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to update vaccine schedule. Please try again.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
