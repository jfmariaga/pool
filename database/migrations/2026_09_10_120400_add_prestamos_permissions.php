<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private array $permisos = [
        'ver prestamos',
        'crear prestamos',
        'editar prestamos',
        'eliminar prestamos',
        'ver prestamo-inversionistas',
        'gestionar prestamo-inversionistas',
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
