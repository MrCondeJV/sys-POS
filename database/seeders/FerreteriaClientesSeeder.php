<?php

namespace Database\Seeders;

use App\Enums\EstadoGeneral;
use App\Enums\TipoDocumentoIdentidad;
use App\Enums\TipoPersona;
use App\Models\Cliente;
use App\Models\Empresa;
use Illuminate\Database\Seeder;

class FerreteriaClientesSeeder extends Seeder
{
    public function run(?Empresa $empresa = null): void
    {
        if (! $empresa) {
            $empresa = Empresa::where('nit', '901234567')->first();
        }

        if (! $empresa) {
            return;
        }

        $clientes = [
            [
                'numero_documento' => '900456789',
                'tipo_persona' => TipoPersona::JURIDICA,
                'tipo_documento' => TipoDocumentoIdentidad::NIT,
                'razon_social' => 'Constructora Arco del Norte S.A.S.',
                'nombre_comercial' => 'Constructora Arco',
                'telefono' => '6013456789',
                'email' => 'compras@constructorarco.com',
                'direccion' => 'Calle 100 # 19-61 Edificio Torre Central Of. 502',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 8000000.00,
                'plazo_dias' => 30,
            ],
            [
                'numero_documento' => '800234567',
                'tipo_persona' => TipoPersona::JURIDICA,
                'tipo_documento' => TipoDocumentoIdentidad::NIT,
                'razon_social' => 'Ingeniería y Obras Civiles Ltda.',
                'nombre_comercial' => 'Ingeniería y Obras',
                'telefono' => '6017890123',
                'email' => 'obras@ingyobras.com.co',
                'direccion' => 'Carrera 68 # 45A-12',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 5000000.00,
                'plazo_dias' => 30,
            ],
            [
                'numero_documento' => '860098765',
                'tipo_persona' => TipoPersona::JURIDICA,
                'tipo_documento' => TipoDocumentoIdentidad::NIT,
                'razon_social' => 'Maderas y Materiales Salgado S.A.S.',
                'nombre_comercial' => 'Materiales Salgado',
                'telefono' => '3114567890',
                'email' => 'pedidos@maderassalgado.com',
                'direccion' => 'Av. Caracas # 32-15 Sur',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 3000000.00,
                'plazo_dias' => 15,
            ],
            [
                'numero_documento' => '901567890',
                'tipo_persona' => TipoPersona::JURIDICA,
                'tipo_documento' => TipoDocumentoIdentidad::NIT,
                'razon_social' => 'Centro de Construcción Rápida S.A.S.',
                'nombre_comercial' => 'ConstruRápida',
                'telefono' => '6018901234',
                'email' => 'adquisiciones@construrapida.com',
                'direccion' => 'Autopista Norte Km 18 Costado Oriental',
                'ciudad' => 'Chía',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 10000000.00,
                'plazo_dias' => 45,
            ],
            [
                'numero_documento' => '79456123',
                'tipo_persona' => TipoPersona::NATURAL,
                'tipo_documento' => TipoDocumentoIdentidad::CC,
                'razon_social' => 'Pedro Antonio García López (Maestro de Obra)',
                'nombre_comercial' => null,
                'telefono' => '3109876543',
                'email' => 'pedro.garcia.obra@gmail.com',
                'direccion' => 'Calle 72 # 22-14 Barrio San Felipe',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 800000.00,
                'plazo_dias' => 15,
            ],
            [
                'numero_documento' => '52789456',
                'tipo_persona' => TipoPersona::NATURAL,
                'tipo_documento' => TipoDocumentoIdentidad::CC,
                'razon_social' => 'Luz Marina Vargas Soto',
                'nombre_comercial' => null,
                'telefono' => '3142345678',
                'email' => 'luz.vargas.decor@hotmail.com',
                'direccion' => 'Carrera 15 # 85-30 Apto 402',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 500000.00,
                'plazo_dias' => 15,
            ],
            [
                'numero_documento' => '80123456',
                'tipo_persona' => TipoPersona::NATURAL,
                'tipo_documento' => TipoDocumentoIdentidad::CC,
                'razon_social' => 'Carlos Hernández Moreno (Contratista Eléctrico)',
                'nombre_comercial' => null,
                'telefono' => '3203456789',
                'email' => 'carlos.hdez.electricos@gmail.com',
                'direccion' => 'Calle 53 # 16-25',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 1500000.00,
                'plazo_dias' => 30,
            ],
            [
                'numero_documento' => '19876543',
                'tipo_persona' => TipoPersona::NATURAL,
                'tipo_documento' => TipoDocumentoIdentidad::CC,
                'razon_social' => 'Rodrigo Salcedo Pinzón (Carpintería Integral)',
                'nombre_comercial' => 'Carpintería Salcedo',
                'telefono' => '3187654321',
                'email' => 'rodrigo.salcedo@carpinteria.co',
                'direccion' => 'Carrera 24 # 63D-18 Barrio 7 de Agosto',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 1200000.00,
                'plazo_dias' => 15,
            ],
            [
                'numero_documento' => '830045678',
                'tipo_persona' => TipoPersona::JURIDICA,
                'tipo_documento' => TipoDocumentoIdentidad::NIT,
                'razon_social' => 'Ferretería y Pinturas La Económica Ltda.',
                'nombre_comercial' => 'La Económica Ferretera',
                'telefono' => '6012345678',
                'email' => 'gerencia@laeconomica.com.co',
                'direccion' => 'Calle 13 # 28-40',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 2000000.00,
                'plazo_dias' => 30,
            ],
            [
                'numero_documento' => '860123789',
                'tipo_persona' => TipoPersona::JURIDICA,
                'tipo_documento' => TipoDocumentoIdentidad::NIT,
                'razon_social' => 'Talleres Mecánicos del Norte Ltda.',
                'nombre_comercial' => 'Talleres del Norte',
                'telefono' => '6016789012',
                'email' => 'mantenimiento@talleresnorte.com',
                'direccion' => 'Calle 166 # 20-35',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 1500000.00,
                'plazo_dias' => 15,
            ],
            [
                'numero_documento' => '41234567',
                'tipo_persona' => TipoPersona::NATURAL,
                'tipo_documento' => TipoDocumentoIdentidad::CC,
                'razon_social' => 'Martha Cecilia Rojas Cifuentes',
                'nombre_comercial' => null,
                'telefono' => '3168901234',
                'email' => 'martha.rojas.c@gmail.com',
                'direccion' => 'Calle 140 # 11-45 Apto 501',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 0.00,
                'plazo_dias' => 0,
            ],
            [
                'numero_documento' => '901890123',
                'tipo_persona' => TipoPersona::JURIDICA,
                'tipo_documento' => TipoDocumentoIdentidad::NIT,
                'razon_social' => 'Grupo Inmobiliario Torres S.A.S.',
                'nombre_comercial' => 'Torres Inmobiliaria & Obras',
                'telefono' => '6019012345',
                'email' => 'proveedores@inmobiliariatorres.co',
                'direccion' => 'Carrera 7 # 71-21 Torre A Piso 12',
                'ciudad' => 'Bogotá',
                'departamento' => 'Cundinamarca',
                'cupo_credito' => 15000000.00,
                'plazo_dias' => 60,
            ],
        ];

        foreach ($clientes as $data) {
            Cliente::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'numero_documento' => $data['numero_documento'],
                ],
                array_merge($data, [
                    'empresa_id' => $empresa->id,
                    'estado' => EstadoGeneral::ACTIVO,
                    'es_predeterminado' => false,
                ])
            );
        }
    }
}
