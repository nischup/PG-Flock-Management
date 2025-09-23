<?php

namespace App\Http\Controllers\VaccineSchedule;

use App\Http\Controllers\Controller;
use App\Models\Master\Batch;
use App\Models\Master\BreedType;
use App\Models\Master\Company;
use App\Models\Master\Disease;
use App\Models\Master\Flock;
use App\Models\Master\Project;
use App\Models\Master\Shed;
use App\Models\Master\Vaccine;
use App\Models\Master\VaccineType;
use Carbon\Carbon;
use Inertia\Inertia;

class UpcomingVaccineController extends Controller
{
    /**
     * Display a listing of upcoming vaccines.
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

        $flocks = Flock::where('status', 1)
            ->orderBy('name')
            ->get();

        $batches = Batch::where('status', 1)
            ->orderBy('name')
            ->get();

        // Get current date and next 30 days for upcoming vaccines
        $today = Carbon::today();
        $next30Days = Carbon::today()->addDays(30);

        // Fetch vaccine schedules with upcoming vaccination dates
        $upcomingVaccines = \App\Models\VaccineSchedule::with([
            'company',
            'project',
            'flock',
            'shed',
            'batch',
            'breedType',
            'vaccineScheduleDetails' => function ($query) use ($today, $next30Days) {
                $query->whereBetween('next_vaccination_date', [$today, $next30Days])
                    ->where('is_active', 1);
            },
            'vaccineScheduleDetails.disease',
            'vaccineScheduleDetails.vaccine',
        ])
            ->whereHas('vaccineScheduleDetails', function ($query) use ($today, $next30Days) {
                $query->whereBetween('next_vaccination_date', [$today, $next30Days])
                    ->where('is_active', 1);
            })
            ->latest()
            ->get();

        // Transform the data to match the expected format
        $transformedVaccines = $upcomingVaccines->map(function ($schedule) {
            return [
                'id' => $schedule->id,
                'company_id' => $schedule->company_id,
                'company_name' => $schedule->company->name ?? '',
                'project_id' => $schedule->project_id,
                'project_name' => $schedule->project->name ?? '',
                'project_code' => $schedule->project->code ?? '',
                'flock_id' => $schedule->flock_id,
                'flock_name' => $schedule->flock->name ?? '',
                'flock_code' => $schedule->flock->code ?? '',
                'flock_no' => $schedule->flock_no ?? '',
                'shed_id' => $schedule->shed_id,
                'shed_name' => $schedule->shed->name ?? '',
                'batch_id' => $schedule->batch_id,
                'batch_name' => $schedule->batch->name ?? '',
                'batch_no' => $schedule->batch_no ?? '',
                'breed_type_id' => $schedule->breed_type_id,
                'breed_type_name' => $schedule->breedType->name ?? '',
                'status' => $schedule->status,
                'created_at' => $schedule->created_at,
                'updated_at' => $schedule->updated_at,
                'details' => $schedule->vaccineScheduleDetails->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'disease_id' => $detail->disease_id,
                        'disease_name' => $detail->disease->name ?? '',
                        'vaccine_id' => $detail->vaccine_id,
                        'vaccine_name' => $detail->vaccine->name ?? '',
                        'age' => $detail->age,
                        'vaccination_date' => $detail->vaccination_date,
                        'next_vaccination_date' => $detail->next_vaccination_date,
                        'status' => $detail->status,
                        'notes' => $detail->notes,
                        'administered_by' => $detail->administered_by,
                        'is_active' => $detail->is_active,
                    ];
                }),
            ];
        });

        return Inertia::render('upcoming-vaccine/Index', [
            'vaccineTypes' => $vaccineTypes->map(fn ($vt) => [
                'id' => $vt->id,
                'name' => $vt->name,
            ])->toArray(),
            'companies' => $companies->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
            ])->toArray(),
            'projects' => $projects->map(fn ($p) => [
                'id' => $p->id,
                'company_id' => $p->company_id,
                'name' => $p->name,
                'code' => $p->code,
            ])->toArray(),
            'sheds' => $sheds->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
            ])->toArray(),
            'breedTypes' => $breedTypes->map(fn ($bt) => [
                'id' => $bt->id,
                'name' => $bt->name,
            ])->toArray(),
            'diseases' => $diseases->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
            ])->toArray(),
            'vaccines' => $vaccines->map(fn ($v) => [
                'id' => $v->id,
                'name' => $v->name,
            ])->toArray(),
            'flocks' => $flocks->map(fn ($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'code' => $f->code,
            ])->toArray(),
            'batches' => $batches->map(fn ($b) => [
                'id' => $b->id,
                'name' => $b->name,
            ])->toArray(),
            'upcomingVaccines' => $transformedVaccines->toArray(),
        ]);
    }
}
