<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SociosPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'SuperAdmin']);

        $permisos = [
            'ver socios',
            'crear socios',
            'editar socios',
            'eliminar socios',
        ];

        foreach ($permisos as $nombre) {
            $permiso = Permission::firstOrCreate(['name' => $nombre]);

            if (! $permiso->hasRole($roleSuperAdmin)) {
                $permiso->assignRole($roleSuperAdmin);
            }
        }
    }
}
