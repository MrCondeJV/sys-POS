<?php

namespace Database\Seeders;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoDocumentoIdentidad;
use App\Models\Empresa;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Sembrar roles y permisos del sistema
        $this->call(RolesAndPermissionsSeeder::class);

        // 2. Crear Empresa Demo
        $empresa = Empresa::firstOrCreate(
            ['nit' => '900123456'],
            [
                'nombre_comercial' => 'Comercio Demo POS',
                'razon_social' => 'Comercio Demo POS S.A.S.',
                'tipo_documento' => TipoDocumentoIdentidad::NIT,
                'dv' => '1',
                'email' => 'admin@pos.com',
                'telefono' => '3001234567',
                'direccion' => 'Calle 10 # 20-30',
                'ciudad' => 'Medellín',
                'departamento' => 'Antioquia',
                'moneda' => 'COP',
                'simbolo_moneda' => '$',
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );

        // Crear roles específicos para la empresa demo
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresa);

        // 3. Crear Sucursal Principal
        $sucursal = Sucursal::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nombre' => 'Sede Principal Centro'],
            [
                'codigo' => 'SUC-001',
                'direccion' => 'Calle 10 # 20-30',
                'ciudad' => 'Medellín',
                'departamento' => 'Antioquia',
                'es_principal' => true,
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );

        // 4. Usuario Super Administrador
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@pos.com'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('password'),
                'empresa_id' => null,
                'cargo' => 'Super Admin',
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );
        setPermissionsTeamId(null);
        if (! $superAdmin->hasRole(RolSistema::SUPER_ADMIN->value)) {
            $superAdmin->assignRole(RolSistema::SUPER_ADMIN->value);
        }

        // 5. Usuario Administrador de Empresa
        $adminEmpresa = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Carlos Administrador',
                'password' => Hash::make('password'),
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
                'cargo' => 'Gerente General',
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );
        setPermissionsTeamId($empresa->id);
        if (! $adminEmpresa->hasRole(RolSistema::ADMIN_EMPRESA->value)) {
            $adminEmpresa->assignRole(RolSistema::ADMIN_EMPRESA->value);
        }

        // 6. Usuario Cajero
        $cajero = User::firstOrCreate(
            ['email' => 'cajero@pos.com'],
            [
                'name' => 'Ana Cajera',
                'password' => Hash::make('password'),
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursal->id,
                'cargo' => 'Cajera Principal',
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );
        setPermissionsTeamId($empresa->id);
        if (! $cajero->hasRole(RolSistema::CAJERO->value)) {
            $cajero->assignRole(RolSistema::CAJERO->value);
        }
    }
}
