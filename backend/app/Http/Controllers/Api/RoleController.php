<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::withCount('users')->orderBy('name')->get();

        return response()->json([
            'data' => $roles,
            'available_pages' => Role::PAGES,
        ]);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        // Yönetim ekranından yalnızca normal rol açılabilir; admin rolü seed ile gelir.
        $role = Role::create([...$request->validated(), 'is_admin' => false]);

        return response()->json(['data' => $role], 201);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        if ($role->is_admin) {
            return response()->json([
                'message' => 'Admin rolü düzenlenemez.',
            ], 422);
        }

        $role->update($request->validated());

        return response()->json(['data' => $role]);
    }

    public function destroy(Role $role): JsonResponse
    {
        if ($role->is_admin) {
            return response()->json(['message' => 'Admin rolü silinemez.'], 422);
        }

        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'Bu role bağlı kullanıcılar var. Önce kullanıcıları başka bir role taşıyın.',
            ], 422);
        }

        $role->delete();

        return response()->json(['message' => 'Rol silindi.']);
    }
}
