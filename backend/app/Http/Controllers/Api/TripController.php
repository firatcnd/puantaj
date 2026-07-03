<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTripRequest;
use App\Http\Requests\UpdateTripRequest;
use App\Http\Resources\TripResource;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $trips = Trip::with('rates.position')
            ->search($request->query('search'))
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->boolean('is_active')))
            ->orderBy('name')
            ->paginate($request->integer('per_page', 10));

        return TripResource::collection($trips);
    }

    public function store(StoreTripRequest $request): TripResource
    {
        $trip = DB::transaction(function () use ($request) {
            $trip = Trip::create($request->safe()->except('rates'));
            $trip->rates()->createMany($request->validated('rates'));

            return $trip;
        });

        return new TripResource($trip->load('rates.position'));
    }

    public function show(Trip $trip): TripResource
    {
        return new TripResource($trip->load('rates.position'));
    }

    public function update(UpdateTripRequest $request, Trip $trip): TripResource
    {
        DB::transaction(function () use ($request, $trip) {
            $trip->update($request->safe()->except('rates'));

            // Ücretler formda bütün olarak yönetildiği için tam senkronizasyon yapılır.
            // Mevcut puantaj satırları ücreti snapshot olarak taşıdığından geçmiş bozulmaz.
            $trip->rates()->delete();
            $trip->rates()->createMany($request->validated('rates'));
        });

        return new TripResource($trip->load('rates.position'));
    }

    public function destroy(Trip $trip): JsonResponse
    {
        $trip->delete(); // soft delete

        return response()->json(['message' => 'Sefer silindi.']);
    }
}
