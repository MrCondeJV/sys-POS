<?php

namespace Database\Seeders;

use App\Enums\EstadoCaja;
use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoDocumentoIdentidad;
use App\Models\Caja;
use App\Models\Empresa;
use App\Models\Proveedor;
use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FerreteriaDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Empresa Ferretería Demo
        $empresa = Empresa::firstOrCreate(
            ['nit' => '901234567'],
            [
                'nombre_comercial' => 'Ferretería y Construcciones El Maestro',
                'razon_social' => 'Ferretería y Construcciones El Maestro S.A.S.',
                'tipo_documento' => TipoDocumentoIdentidad::NIT,
                'dv' => '8',
                'email' => 'admin@ferreteria.com',
                'telefono' => '6017654321',
                'direccion' => 'Carrera 7 # 15-22 Centro',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'moneda' => 'COP',
                'simbolo_moneda' => '$',
                'configuraciones' => [
                    'color_primario' => 'orange',
                ],
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );

        // 2. Roles y Permisos para el Tenant Ferretería
        RolesAndPermissionsSeeder::crearRolesParaEmpresa($empresa);

        // 3. Crear Sucursales
        $sucursalCentro = Sucursal::firstOrCreate(
            ['empresa_id' => $empresa->id, 'codigo' => 'FM-001'],
            [
                'nombre' => 'Sede Principal Centro',
                'direccion' => 'Carrera 7 # 15-22 Centro',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'telefono' => '6017654321',
                'es_principal' => true,
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );

        $sucursalNorte = Sucursal::firstOrCreate(
            ['empresa_id' => $empresa->id, 'codigo' => 'FM-002'],
            [
                'nombre' => 'Sede Norte Suba',
                'direccion' => 'Avenida Suba # 114-48',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'telefono' => '6016890123',
                'es_principal' => false,
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );

        // 4. Cajas de Punto de Venta
        Caja::firstOrCreate(
            ['empresa_id' => $empresa->id, 'sucursal_id' => $sucursalCentro->id, 'codigo' => 'CAJ-01'],
            [
                'nombre' => 'Caja #1 Principal Centro',
                'estado' => EstadoCaja::ACTIVA,
            ]
        );

        Caja::firstOrCreate(
            ['empresa_id' => $empresa->id, 'sucursal_id' => $sucursalNorte->id, 'codigo' => 'CAJ-02'],
            [
                'nombre' => 'Caja #1 Sede Norte Suba',
                'estado' => EstadoCaja::ACTIVA,
            ]
        );

        // 5. Usuarios Operativos y Administrativos
        // A) Administrador de Empresa
        $admin = User::firstOrCreate(
            ['email' => 'admin@ferreteria.com'],
            [
                'name' => 'Ricardo Maestro (Gerente)',
                'password' => Hash::make('password'),
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursalCentro->id,
                'cargo' => 'Gerente General',
                'telefono' => '3101234567',
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );
        setPermissionsTeamId($empresa->id);
        if (! $admin->hasRole(RolSistema::ADMIN_EMPRESA->value)) {
            $admin->assignRole(RolSistema::ADMIN_EMPRESA->value);
        }

        // B) Cajera Sede Principal
        $cajera1 = User::firstOrCreate(
            ['email' => 'cajero1@ferreteria.com'],
            [
                'name' => 'Valentina Ríos (Cajera Centro)',
                'password' => Hash::make('password'),
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursalCentro->id,
                'cargo' => 'Cajera Principal',
                'telefono' => '3129876543',
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );
        setPermissionsTeamId($empresa->id);
        if (! $cajera1->hasRole(RolSistema::CAJERO->value)) {
            $cajera1->assignRole(RolSistema::CAJERO->value);
        }

        // C) Cajero Sede Norte
        $cajero2 = User::firstOrCreate(
            ['email' => 'cajero2@ferreteria.com'],
            [
                'name' => 'Jorge Bermúdez (Cajero Norte)',
                'password' => Hash::make('password'),
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursalNorte->id,
                'cargo' => 'Cajero Sede Norte',
                'telefono' => '3156789012',
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );
        setPermissionsTeamId($empresa->id);
        if (! $cajero2->hasRole(RolSistema::CAJERO->value)) {
            $cajero2->assignRole(RolSistema::CAJERO->value);
        }

        // D) Vendedor Comercial
        $vendedor = User::firstOrCreate(
            ['email' => 'vendedor@ferreteria.com'],
            [
                'name' => 'Andrés Mora (Asesor Comercial)',
                'password' => Hash::make('password'),
                'empresa_id' => $empresa->id,
                'sucursal_id' => $sucursalCentro->id,
                'cargo' => 'Asesor Comercial y Mostrador',
                'telefono' => '3183456789',
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );
        setPermissionsTeamId($empresa->id);
        if (! $vendedor->hasRole(RolSistema::VENDEDOR->value)) {
            $vendedor->assignRole(RolSistema::VENDEDOR->value);
        }

        // 6. Proveedores Especializados de Ferretería
        $proveedores = [
            [
                'numero_documento' => '900111222',
                'razon_social' => 'Distribuidora Stanley Black & Decker Colombia S.A.S.',
                'nombre_contacto' => 'Gustavo Adolfo Pinzón',
                'telefono' => '6013451122',
                'email' => 'pedidos.colombia@stanleytools.com',
                'direccion' => 'Zona Franca Fontibón Edificio 8',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
            ],
            [
                'numero_documento' => '900333444',
                'razon_social' => 'Robert Bosch Ltda. Colombia',
                'nombre_contacto' => 'Clara Inés Restrepo',
                'telefono' => '6017894455',
                'email' => 'ventas.herramientas@co.bosch.com',
                'direccion' => 'Autopista Norte # 108-27 Piso 4',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
            ],
            [
                'numero_documento' => '860055667',
                'razon_social' => 'Compañía Global de Pinturas Pintuco S.A.',
                'nombre_contacto' => 'Mauricio Echeverri',
                'telefono' => '6043705000',
                'email' => 'pedidos@pintuco.com',
                'direccion' => 'Vía 40 # 73-290',
                'ciudad' => 'Medellín',
                'departamento' => 'Antioquia',
            ],
            [
                'numero_documento' => '890900682',
                'razon_social' => 'Cementos Argos S.A.',
                'nombre_contacto' => 'Luz Adriana Meza',
                'telefono' => '6043198700',
                'email' => 'canalferretero@argos.com.co',
                'direccion' => 'Carrera 43A # 1A Sur-143',
                'ciudad' => 'Medellín',
                'departamento' => 'Antioquia',
            ],
            [
                'numero_documento' => '800188999',
                'razon_social' => 'Tuberías y Sistemas Pavco Wavin Colombia',
                'nombre_contacto' => 'Felipe Cárdenas',
                'telefono' => '6017825000',
                'email' => 'servicioalcliente@wavin.com',
                'direccion' => 'Autopista Sur # 71-75',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
            ],
            [
                'numero_documento' => '860023765',
                'razon_social' => '3M Colombia S.A.',
                'nombre_contacto' => 'Sandra Milena Beltrán',
                'telefono' => '6014161655',
                'email' => 'seguridad.industrial@3m.com',
                'direccion' => 'Avenida El Dorado # 69-63',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
            ],
        ];

        foreach ($proveedores as $p) {
            Proveedor::firstOrCreate(
                ['empresa_id' => $empresa->id, 'numero_documento' => $p['numero_documento']],
                array_merge($p, [
                    'empresa_id' => $empresa->id,
                    'tipo_documento' => TipoDocumentoIdentidad::NIT,
                    'estado' => EstadoGeneral::ACTIVO,
                ])
            );
        }

        // 7. Invocar Catálogo de Productos y Clientes
        \App\Support\Tenancy\CompanyContext::setCompany($empresa);
        (new FerreteriaProductosSeeder)->run($empresa);
        (new FerreteriaClientesSeeder)->run($empresa);
    }
}
