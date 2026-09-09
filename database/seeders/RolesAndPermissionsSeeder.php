<?php

namespace Database\Seeders;

use App\Enums\PermisoSistema;
use App\Enums\RolSistema;
use App\Models\Empresa;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar caché de permisos de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Crear todos los permisos del sistema
        foreach (PermisoSistema::cases() as $permiso) {
            Permission::firstOrCreate([
                'name' => $permiso->value,
                'guard_name' => 'web',
            ]);
        }

        // 2. Crear rol global SUPER_ADMIN (sin empresa_id asignada)
        $superAdminRole = Role::firstOrCreate([
            'name' => RolSistema::SUPER_ADMIN->value,
            'guard_name' => 'web',
            'empresa_id' => null,
        ]);
        $superAdminRole->syncPermissions(Permission::all());

        // 3. Crear roles base para todas las empresas existentes
        foreach (Empresa::all() as $empresa) {
            self::crearRolesParaEmpresa($empresa);
        }
    }

    /**
     * Crea los roles estándares para una empresa específica y asigna permisos por defecto.
     */
    public static function crearRolesParaEmpresa(Empresa $empresa): void
    {
        setPermissionsTeamId($empresa->id);

        // ADMIN_EMPRESA
        $adminEmpresa = Role::firstOrCreate([
            'name' => RolSistema::ADMIN_EMPRESA->value,
            'guard_name' => 'web',
            'empresa_id' => $empresa->id,
        ]);
        $adminEmpresa->syncPermissions([
            PermisoSistema::VENTAS_VER->value,
            PermisoSistema::VENTAS_CREAR->value,
            PermisoSistema::VENTAS_ANULAR->value,
            PermisoSistema::VENTAS_DEVOLVER->value,
            PermisoSistema::PRODUCTOS_VER->value,
            PermisoSistema::PRODUCTOS_CREAR->value,
            PermisoSistema::PRODUCTOS_EDITAR->value,
            PermisoSistema::PRODUCTOS_ELIMINAR->value,
            PermisoSistema::INVENTARIO_VER->value,
            PermisoSistema::INVENTARIO_AJUSTAR->value,
            PermisoSistema::CAJA_ABRIR->value,
            PermisoSistema::CAJA_CERRAR->value,
            PermisoSistema::COMPRAS_VER->value,
            PermisoSistema::COMPRAS_CREAR->value,
            PermisoSistema::COMPRAS_ANULAR->value,
            PermisoSistema::PROVEEDORES_VER->value,
            PermisoSistema::PROVEEDORES_CREAR->value,
            PermisoSistema::PROVEEDORES_EDITAR->value,
            PermisoSistema::PROVEEDORES_ELIMINAR->value,
            PermisoSistema::CLIENTES_VER->value,
            PermisoSistema::CLIENTES_CREAR->value,
            PermisoSistema::CLIENTES_EDITAR->value,
            PermisoSistema::CLIENTES_ELIMINAR->value,
            PermisoSistema::REPORTES_VER->value,
            PermisoSistema::EMPRESA_GESTIONAR->value,
            PermisoSistema::SUCURSALES_GESTIONAR->value,
            PermisoSistema::USUARIOS_VER->value,
            PermisoSistema::USUARIOS_GESTIONAR->value,
        ]);

        // ADMIN_SUCURSAL
        $adminSucursal = Role::firstOrCreate([
            'name' => RolSistema::ADMIN_SUCURSAL->value,
            'guard_name' => 'web',
            'empresa_id' => $empresa->id,
        ]);
        $adminSucursal->syncPermissions([
            PermisoSistema::VENTAS_VER->value,
            PermisoSistema::VENTAS_CREAR->value,
            PermisoSistema::VENTAS_ANULAR->value,
            PermisoSistema::VENTAS_DEVOLVER->value,
            PermisoSistema::PRODUCTOS_VER->value,
            PermisoSistema::PRODUCTOS_CREAR->value,
            PermisoSistema::PRODUCTOS_EDITAR->value,
            PermisoSistema::INVENTARIO_VER->value,
            PermisoSistema::INVENTARIO_AJUSTAR->value,
            PermisoSistema::CAJA_ABRIR->value,
            PermisoSistema::CAJA_CERRAR->value,
            PermisoSistema::COMPRAS_VER->value,
            PermisoSistema::COMPRAS_CREAR->value,
            PermisoSistema::COMPRAS_ANULAR->value,
            PermisoSistema::PROVEEDORES_VER->value,
            PermisoSistema::PROVEEDORES_CREAR->value,
            PermisoSistema::PROVEEDORES_EDITAR->value,
            PermisoSistema::CLIENTES_VER->value,
            PermisoSistema::CLIENTES_CREAR->value,
            PermisoSistema::CLIENTES_EDITAR->value,
            PermisoSistema::REPORTES_VER->value,
            PermisoSistema::SUCURSALES_GESTIONAR->value,
        ]);

        // CAJERO
        $cajero = Role::firstOrCreate([
            'name' => RolSistema::CAJERO->value,
            'guard_name' => 'web',
            'empresa_id' => $empresa->id,
        ]);
        $cajero->syncPermissions([
            PermisoSistema::VENTAS_VER->value,
            PermisoSistema::VENTAS_CREAR->value,
            PermisoSistema::PRODUCTOS_VER->value,
            PermisoSistema::INVENTARIO_VER->value,
            PermisoSistema::CAJA_ABRIR->value,
            PermisoSistema::CAJA_CERRAR->value,
            PermisoSistema::CLIENTES_VER->value,
            PermisoSistema::CLIENTES_CREAR->value,
        ]);

        // VENDEDOR
        $vendedor = Role::firstOrCreate([
            'name' => RolSistema::VENDEDOR->value,
            'guard_name' => 'web',
            'empresa_id' => $empresa->id,
        ]);
        $vendedor->syncPermissions([
            PermisoSistema::VENTAS_VER->value,
            PermisoSistema::VENTAS_CREAR->value,
            PermisoSistema::PRODUCTOS_VER->value,
            PermisoSistema::CLIENTES_VER->value,
            PermisoSistema::CLIENTES_CREAR->value,
        ]);

        // CONTADOR
        $contador = Role::firstOrCreate([
            'name' => RolSistema::CONTADOR->value,
            'guard_name' => 'web',
            'empresa_id' => $empresa->id,
        ]);
        $contador->syncPermissions([
            PermisoSistema::VENTAS_VER->value,
            PermisoSistema::COMPRAS_VER->value,
            PermisoSistema::PROVEEDORES_VER->value,
            PermisoSistema::CLIENTES_VER->value,
            PermisoSistema::INVENTARIO_VER->value,
            PermisoSistema::REPORTES_VER->value,
        ]);
    }
}
