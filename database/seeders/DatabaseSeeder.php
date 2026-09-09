<?php

namespace Database\Seeders;

use App\Enums\EstadoGeneral;
use App\Enums\RolSistema;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoMovimientoInventario;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Inventario;
use App\Models\Marca;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Sucursal;
use App\Models\UnidadMedida;
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

        // 7. Catálogos Base para Empresa Demo
        $catBebidas = Categoria::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nombre' => 'Bebidas y Refrescos'],
            ['descripcion' => 'Gaseosas, jugos y aguas', 'activo' => true]
        );
        $catAbarrotes = Categoria::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nombre' => 'Abarrotes y Despensa'],
            ['descripcion' => 'Granos, aceites, lácteos y enlatados', 'activo' => true]
        );
        $catFerreteria = Categoria::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nombre' => 'Herramientas y Ferretería'],
            ['descripcion' => 'Tornillos, herramientas y fijaciones', 'activo' => true]
        );

        $marcaPostobon = Marca::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nombre' => 'Postobón'],
            ['descripcion' => 'Bebidas nacionales', 'activo' => true]
        );
        $marcaStanley = Marca::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nombre' => 'Stanley'],
            ['descripcion' => 'Herramientas profesionales', 'activo' => true]
        );
        $marcaDiana = Marca::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nombre' => 'Arroz Diana'],
            ['descripcion' => 'Alimentos', 'activo' => true]
        );

        $und = UnidadMedida::firstOrCreate(
            ['empresa_id' => $empresa->id, 'codigo' => 'UND'],
            ['nombre' => 'Unidad', 'activo' => true]
        );
        $kg = UnidadMedida::firstOrCreate(
            ['empresa_id' => $empresa->id, 'codigo' => 'KG'],
            ['nombre' => 'Kilogramo', 'activo' => true]
        );

        // 8. Productos de Prueba
        Producto::firstOrCreate(
            ['empresa_id' => $empresa->id, 'codigo' => 'BEB-001'],
            [
                'nombre' => 'Gaseosa Manzana Postobón 1.5L',
                'codigo_barras' => '7702090012345',
                'categoria_id' => $catBebidas->id,
                'marca_id' => $marcaPostobon->id,
                'unidad_medida_id' => $und->id,
                'precio_compra' => 3200,
                'precio_venta' => 4800,
                'precio_mayorista' => 4200,
                'stock' => 48,
                'stock_minimo' => 12,
                'iva' => 19,
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );

        Producto::firstOrCreate(
            ['empresa_id' => $empresa->id, 'codigo' => 'ABA-001'],
            [
                'nombre' => 'Arroz Blanco Diana 1000g',
                'codigo_barras' => '7702511000012',
                'categoria_id' => $catAbarrotes->id,
                'marca_id' => $marcaDiana->id,
                'unidad_medida_id' => $kg->id,
                'precio_compra' => 3600,
                'precio_venta' => 4500,
                'precio_mayorista' => 4100,
                'stock' => 5, // Bajo stock para probar alerta
                'stock_minimo' => 10,
                'iva' => 0,
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );

        Producto::firstOrCreate(
            ['empresa_id' => $empresa->id, 'codigo' => 'FER-001'],
            [
                'nombre' => 'Cinta Métrica Stanley PowerLock 5m',
                'codigo_barras' => '076174332156',
                'categoria_id' => $catFerreteria->id,
                'marca_id' => $marcaStanley->id,
                'unidad_medida_id' => $und->id,
                'precio_compra' => 16000,
                'precio_venta' => 26000,
                'precio_mayorista' => 22000,
                'stock' => 15,
                'stock_minimo' => 3,
                'iva' => 19,
                'estado' => EstadoGeneral::ACTIVO,
            ]
        );

        // 9. Inicialización de Inventario y Kardex por Sucursal para Productos Demo
        $todosLosProductos = Producto::where('empresa_id', $empresa->id)->get();
        foreach ($todosLosProductos as $p) {
            $inv = Inventario::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'sucursal_id' => $sucursal->id,
                    'producto_id' => $p->id,
                ],
                [
                    'stock' => $p->stock,
                    'stock_minimo' => $p->stock_minimo,
                    'ubicacion' => 'Pasillo Principal - Estante 1',
                ]
            );

            // Asentar movimiento inicial si no existe en el Kardex
            MovimientoInventario::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'sucursal_id' => $sucursal->id,
                    'producto_id' => $p->id,
                    'tipo' => TipoMovimientoInventario::ENTRADA_COMPRA->value,
                ],
                [
                    'user_id' => $adminEmpresa->id,
                    'cantidad' => $p->stock,
                    'costo_unitario' => $p->precio_compra,
                    'stock_anterior' => 0,
                    'stock_posterior' => $p->stock,
                    'referencia' => 'Carga inicial de inventario / Saldo de apertura',
                    'notas' => 'Inventario base registrado durante la configuración del sistema.',
                ]
            );
        }
    }
}
