<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimesheetRequest;
use App\Http\Requests\UpdateTimesheetRequest;
use App\Http\Resources\TimesheetResource;
use App\Models\Timesheet;
use App\Services\TimesheetService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TimesheetController extends Controller
{
    public function __construct(private readonly TimesheetService $service)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $timesheets = Timesheet::with(['personnel.department', 'position'])
            ->withCount('entries')
            ->withSum('entries', 'trip_count')
            ->when($request->query('personnel_id'), fn ($q, $id) => $q->where('personnel_id', $id))
            ->when($request->query('position_id'), fn ($q, $id) => $q->where('position_id', $id))
            ->when($request->query('department_id'), fn ($q, $id) => $q->whereHas(
                'personnel', fn ($sub) => $sub->where('department_id', $id)
            ))
            ->when($request->query('year'), fn ($q, $year) => $q->where('year', $year))
            ->when($request->query('month'), fn ($q, $month) => $q->where('month', $month))
            ->when($request->query('search'), fn ($q, $term) => $q->whereHas(
                'personnel', fn ($sub) => $sub->where('full_name', 'like', "%{$term}%")
                    ->orWhere('registration_no', 'like', "%{$term}%")
            ))
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return TimesheetResource::collection($timesheets);
    }

    public function store(StoreTimesheetRequest $request): TimesheetResource
    {
        $timesheet = $this->service->create(
            $request->safe()->except('entries'),
            $request->validated('entries', [])
        );

        return new TimesheetResource($timesheet);
    }

    public function show(Timesheet $timesheet): TimesheetResource
    {
        return new TimesheetResource(
            $timesheet->load(['personnel.department', 'position', 'entries.trip'])
        );
    }

    public function update(UpdateTimesheetRequest $request, Timesheet $timesheet): TimesheetResource
    {
        $timesheet = $this->service->update(
            $timesheet,
            $request->safe()->except('entries'),
            $request->validated('entries', [])
        );

        return new TimesheetResource($timesheet);
    }

    public function destroy(Timesheet $timesheet): JsonResponse
    {
        $timesheet->delete(); // soft delete

        return response()->json(['message' => 'Puantaj silindi.']);
    }
}
