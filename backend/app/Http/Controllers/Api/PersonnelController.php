<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePersonnelRequest;
use App\Http\Requests\UpdatePersonnelRequest;
use App\Http\Resources\PersonnelResource;
use App\Models\Personnel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PersonnelController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $personnel = Personnel::with(['department', 'position'])
            ->search($request->query('search'))
            ->when($request->query('department_id'), fn ($q, $id) => $q->where('department_id', $id))
            ->when($request->query('position_id'), fn ($q, $id) => $q->where('position_id', $id))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('full_name')
            ->paginate($request->integer('per_page', 10));

        return PersonnelResource::collection($personnel);
    }

    public function store(StorePersonnelRequest $request): PersonnelResource
    {
        $personnel = Personnel::create($request->validated());

        return new PersonnelResource($personnel->load(['department', 'position']));
    }

    public function show(Personnel $personnel): PersonnelResource
    {
        return new PersonnelResource($personnel->load(['department', 'position']));
    }

    public function update(UpdatePersonnelRequest $request, Personnel $personnel): PersonnelResource
    {
        $personnel->update($request->validated());

        return new PersonnelResource($personnel->load(['department', 'position']));
    }

    public function destroy(Personnel $personnel): JsonResponse
    {
        $personnel->delete(); // soft delete

        return response()->json(['message' => 'Personel silindi.']);
    }
}
