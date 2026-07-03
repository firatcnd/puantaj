<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::firstOrCreate(
            ['name' => 'Admin'],
            ['permissions' => Role::PAGES, 'is_admin' => true]
        );

        // Kısıtlı izinli örnek rol: yalnızca dashboard ve puantaj görür
        $puantajci = Role::firstOrCreate(
            ['name' => 'Puantaj Uzmanı'],
            ['permissions' => ['dashboard', 'puantajlar'], 'is_admin' => false]
        );

        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin', 'password' => '123', 'role_id' => $admin->id]
        );

        User::updateOrCreate(
            ['email' => 'puantaj@puantaj.test'],
            ['name' => 'Puantaj Uzmanı', 'password' => 'password', 'role_id' => $puantajci->id]
        );
    }
}
