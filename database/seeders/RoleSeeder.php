<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Deshabilitar las verificaciones de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncar las tablas relacionadas con roles y permisos
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        DB::table('roles')->truncate();
        DB::table('permissions')->truncate();

        // Habilitar nuevamente las verificaciones de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Crear roles
        $roleAdmin = Role::create(['name' => 'Admin']);
        $roleSuperAdmin = Role::create(['name' => 'SuperAdmin']);
        $roleVendedor = Role::create(['name' => 'Vendedor']);

        // Crear permisos para dashboard
        Permission::create(['name' => 'ver dashboard'])->syncRoles([$roleAdmin]);

        // Crear permisos para Usuarios (CRUD completo)
        Permission::create(['name' => 'ver usuarios'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear usuarios'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar usuarios'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar usuarios'])->syncRoles([$roleAdmin]);

        // Crear permisos para Categorias (CRUD completo)
        Permission::create(['name' => 'ver categorias'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear categorias'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar categorias'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar categorias'])->syncRoles([$roleAdmin]);

        // Crear permisos para Productos (CRUD completo)
        Permission::create(['name' => 'ver productos'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear productos'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar productos'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar productos'])->syncRoles([$roleAdmin]);

        // Crear permisos para Proveedores (CRUD completo)
        Permission::create(['name' => 'ver proveedores'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear proveedores'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar proveedores'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar proveedores'])->syncRoles([$roleAdmin]);

        // Crear permisos para Cuentas (CRUD completo)
        Permission::create(['name' => 'ver cuentas'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear cuentas'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar cuentas'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar cuentas'])->syncRoles([$roleAdmin]);

        // Crear permisos para Cierre de Caja (CRUD completo)
        Permission::create(['name' => 'ver cierre-caja'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear cierre-caja'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar cierre-caja'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar cierre-caja'])->syncRoles([$roleAdmin]);

        // Crear permisos para Compras (CRUD completo)
        Permission::create(['name' => 'ver compras'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear compras'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar compras'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar compras'])->syncRoles([$roleAdmin]);

        // Crear permisos para Ventas (sin CRUD completo, solo creación y visualización)
        Permission::create(['name' => 'ver ventas'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear ventas'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar ventas'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar ventas'])->syncRoles([$roleAdmin]);

        // Crear permisos para Ajuste de Inventario (CRUD completo)
        Permission::create(['name' => 'ver ajuste-inventario'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear ajuste-inventario'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar ajuste-inventario'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar ajuste-inventario'])->syncRoles([$roleAdmin]);

        // Crear permisos para Movimientos (CRUD completo)
        Permission::create(['name' => 'ver movimientos'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear movimientos'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar movimientos'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar movimientos'])->syncRoles([$roleAdmin]);

        // Crear permisos para Roles (CRUD completo)
        Permission::create(['name' => 'ver roles'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'crear roles'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'editar roles'])->syncRoles([$roleAdmin]);
        Permission::create(['name' => 'eliminar roles'])->syncRoles([$roleAdmin]);
    }
}
