<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\JsonResponse;

/** Form select'lerinde kullanılan sabit listeler (departman, pozisyon). */
class LookupController extends Controller
{
    public function departments(): JsonResponse
    {
        return response()->json(Department::orderBy('name')->get(['id', 'name']));
    }

    public function positions(): JsonResponse
    {
        return response()->json(Position::orderBy('name')->get(['id', 'name']));
    }
}
