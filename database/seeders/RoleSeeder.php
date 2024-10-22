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
        Permission::create(['name' => 'ver dashboard'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Usuarios (CRUD completo)
        Permission::create(['name' => 'ver usuarios'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear usuarios'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar usuarios'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar usuarios'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Categorias (CRUD completo)
        Permission::create(['name' => 'ver categorias'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear categorias'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar categorias'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar categorias'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Productos (CRUD completo)
        Permission::create(['name' => 'ver productos'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear productos'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar productos'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar productos'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Proveedores (CRUD completo)
        Permission::create(['name' => 'ver proveedores'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear proveedores'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar proveedores'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar proveedores'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Cuentas (CRUD completo)
        Permission::create(['name' => 'ver cuentas'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear cuentas'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar cuentas'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar cuentas'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Cierre de Caja (CRUD completo)
        Permission::create(['name' => 'ver cierre-caja'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear cierre-caja'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar cierre-caja'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar cierre-caja'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Compras (CRUD completo)
        Permission::create(['name' => 'ver compras'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear compras'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar compras'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar compras'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Ventas (sin CRUD completo, solo creación y visualización)
        Permission::create(['name' => 'ver ventas'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear ventas'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar ventas'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar ventas'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Ajuste de Inventario (CRUD completo)
        Permission::create(['name' => 'ver ajuste-inventario'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear ajuste-inventario'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar ajuste-inventario'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar ajuste-inventario'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Movimientos (CRUD completo)
        Permission::create(['name' => 'ver movimientos'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear movimientos'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar movimientos'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar movimientos'])->syncRoles([$roleSuperAdmin]);

        // Crear permisos para Roles (CRUD completo)
        Permission::create(['name' => 'ver roles'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'crear roles'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'editar roles'])->syncRoles([$roleSuperAdmin]);
        Permission::create(['name' => 'eliminar roles'])->syncRoles([$roleSuperAdmin]);
    }
}
