<?php

namespace Database\Seeders;

use App\Enums\EstadoGeneral;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoPersona;
use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (Empresa::withoutGlobalScopes()->get() as $empresa) {
            \App\Support\Tenancy\CompanyContext::runInContext($empresa, function () use ($empresa) {
                // 1. Cliente Predeterminado Obligatorio: CONSUMIDOR FINAL (Estándar DIAN Colombia)
            Cliente::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'numero_documento' => '222222222222',
                ],
                [
                    'tipo_persona' => TipoPersona::NATURAL,
                    'tipo_documento' => TipoDocumentoIdentidad::CC,
                    'razon_social' => 'CONSUMIDOR FINAL',
                    'nombre_comercial' => null,
                    'telefono' => null,
                    'email' => null,
                    'direccion' => 'Mostrador / Ventas Rápidas',
                    'ciudad' => $empresa->ciudad ?? 'Medellín',
                    'departamento' => $empresa->departamento ?? 'Antioquia',
                    'cupo_credito' => 0,
                    'plazo_dias' => 0,
                    'estado' => EstadoGeneral::ACTIVO,
                    'es_predeterminado' => true,
                ]
            );

            // 2. Clientes Demostrativos
            Cliente::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'numero_documento' => '901234567',
                ],
                [
                    'tipo_persona' => TipoPersona::JURIDICA,
                    'tipo_documento' => TipoDocumentoIdentidad::NIT,
                    'razon_social' => 'Constructora & Ferretería Caribe S.A.S.',
                    'nombre_comercial' => 'Ferretería Caribe',
                    'telefono' => '3009876543',
                    'email' => 'compras@ferreteriacaribe.com',
                    'direccion' => 'Zona Industrial Manzana B Lote 4',
                    'ciudad' => 'Coveñas',
                    'departamento' => 'Sucre',
                    'cupo_credito' => 5000000.00,
                    'plazo_dias' => 30,
                    'estado' => EstadoGeneral::ACTIVO,
                    'es_predeterminado' => false,
                ]
            );

            Cliente::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'numero_documento' => '1045678901',
                ],
                [
                    'tipo_persona' => TipoPersona::NATURAL,
                    'tipo_documento' => TipoDocumentoIdentidad::CC,
                    'razon_social' => 'Juan Carlos Pérez Gómez',
                    'nombre_comercial' => null,
                    'telefono' => '3157890123',
                    'email' => 'juan.perez@correo.com',
                    'direccion' => 'Barrio El Carmen Carrera 14 # 25-10',
                    'ciudad' => 'Coveñas',
                    'departamento' => 'Sucre',
                    'cupo_credito' => 800000.00,
                    'plazo_dias' => 15,
                    'estado' => EstadoGeneral::ACTIVO,
                    'es_predeterminado' => false,
                ]
            );

            Cliente::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'numero_documento' => '1037890123',
                ],
                [
                    'tipo_persona' => TipoPersona::NATURAL,
                    'tipo_documento' => TipoDocumentoIdentidad::CC,
                    'razon_social' => 'María Camila Restrepo López',
                    'nombre_comercial' => null,
                    'telefono' => '3201234567',
                    'email' => 'mrestrepo@gmail.com',
                    'direccion' => 'Calle 8 # 12-40',
                    'ciudad' => 'Coveñas',
                    'departamento' => 'Sucre',
                    'cupo_credito' => 0,
                    'plazo_dias' => 0,
                    'estado' => EstadoGeneral::ACTIVO,
                    'es_predeterminado' => false,
                ]
            );
        });
        }
    }
}
