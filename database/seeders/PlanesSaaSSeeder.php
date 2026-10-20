<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanesSaaSSeeder extends Seeder
{
    public function run(): void
    {
        Plan::firstOrCreate(
            ['slug' => 'basico'],
            [
                'nombre' => 'Plan Básico',
                'descripcion' => 'Ideal para pequeños negocios y comercios independientes.',
                'precio_mensual' => 49900,
                'precio_anual' => 499000,
                'moneda' => 'COP',
                'limite_sucursales' => 1,
                'limite_usuarios' => 2,
                'permite_facturacion_electronica' => false,
                'permite_api' => false,
                'permite_farmacia' => false,
                'permite_ferreteria' => false,
                'dias_prueba' => 14,
                'orden_visual' => 1,
                'activo' => true,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'profesional'],
            [
                'nombre' => 'Plan Profesional',
                'descripcion' => 'Para comercios en crecimiento que requieren facturación electrónica y múltiples usuarios.',
                'precio_mensual' => 99900,
                'precio_anual' => 999000,
                'moneda' => 'COP',
                'limite_sucursales' => 3,
                'limite_usuarios' => 5,
                'permite_facturacion_electronica' => true,
                'permite_api' => false,
                'permite_farmacia' => true,
                'permite_ferreteria' => true,
                'dias_prueba' => 14,
                'orden_visual' => 2,
                'activo' => true,
            ]
        );

        Plan::firstOrCreate(
            ['slug' => 'empresarial'],
            [
                'nombre' => 'Plan Empresarial',
                'descripcion' => 'Para empresas consolidadas con múltiples sedes, facturación electrónica masiva e integración API.',
                'precio_mensual' => 199900,
                'precio_anual' => 1999000,
                'moneda' => 'COP',
                'limite_sucursales' => 0, // ilimitado
                'limite_usuarios' => 0,   // ilimitado
                'permite_facturacion_electronica' => true,
                'permite_api' => true,
                'permite_farmacia' => true,
                'permite_ferreteria' => true,
                'dias_prueba' => 14,
                'orden_visual' => 3,
                'activo' => true,
            ]
        );
    }
}
