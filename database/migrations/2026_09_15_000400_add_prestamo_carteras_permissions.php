<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permisos = [
        'ver prestamo-carteras',
        'gestionar prestamo-carteras',
    ];

    public function up(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = Role::whereIn('name', ['SuperAdmin', 'Admin'])->get();

        foreach ($this->permisos as $nombre) {
            $permiso = Permission::firstOrCreate(['name' => $nombre, 'guard_name' => 'web']);
            foreach ($roles as $role) {
                $role->givePermissionTo($permiso);
            }
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        Permission::whereIn('name', $this->permisos)->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
