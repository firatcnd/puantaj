<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Personnel;
use App\Models\Position;
use App\Models\Trip;
use Illuminate\Http\JsonResponse;

/**
 * Form select'lerinde kullanılan hafif listeler.
 * CRUD yetkisi gerektirmez; oturum açmış her kullanıcı erişebilir
 * (örn. yalnızca puantaj izni olan rol, formda personel/sefer seçebilmelidir).
 */
class LookupController extends Controller
{
    public function departments(): JsonResponse
    {
        return response()->json(Department::orderBy('name')->get(['id', 'name']));
    }

    public function positions(): JsonResponse
    {
        return response()->json(Position::orderBy('name')->get(['id', 'name', 'department_id']));
    }

    public function personnel(): JsonResponse
    {
        return response()->json(
            Personnel::with('position:id,name')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'registration_no', 'position_id', 'is_active'])
                ->map(fn (Personnel $person) => [
                    'id' => $person->id,
                    'full_name' => $person->full_name,
                    'registration_no' => $person->registration_no,
                    'position_id' => $person->position_id,
                    'position' => $person->position->name,
                    'is_active' => $person->is_active,
                ])
        );
    }

    public function trips(): JsonResponse
    {
        return response()->json(
            Trip::with('rates:id,trip_id,position_id,rate')
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'is_active'])
                ->map(fn (Trip $trip) => [
                    'id' => $trip->id,
                    'name' => $trip->name,
                    'code' => $trip->code,
                    'is_active' => $trip->is_active,
                    'rates' => $trip->rates->map(fn ($rate) => [
                        'position_id' => $rate->position_id,
                        'rate' => (float) $rate->rate,
                    ]),
                ])
        );
    }
}
