<?php

namespace Database\Seeders;

use App\Enums\EstadoGeneral;
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

class FerreteriaProductosSeeder extends Seeder
{
    public function run(?Empresa $empresa = null): void
    {
        if (! $empresa) {
            $empresa = Empresa::where('nit', '901234567')->first();
        }

        if (! $empresa) {
            return;
        }

        $sucursales = Sucursal::where('empresa_id', $empresa->id)->get();
        $adminUser = User::where('empresa_id', $empresa->id)->first() ?? User::first();

        // 1. Unidades de Medida
        $unidades = [
            'UND' => 'Unidad',
            'MT'  => 'Metro',
            'BTO' => 'Bulto',
            'GL'  => 'Galón',
            'KG'  => 'Kilogramo',
            'RLL' => 'Rollo',
            'PAR' => 'Par',
            'CAJ' => 'Caja',
            'PAQ' => 'Paquete',
        ];
        $undModels = [];
        foreach ($unidades as $cod => $nom) {
            $undModels[$cod] = UnidadMedida::firstOrCreate(
                ['empresa_id' => $empresa->id, 'codigo' => $cod],
                ['nombre' => $nom, 'activo' => true]
            );
        }

        // 2. Marcas
        $marcas = [
            'Stanley' => 'Herramientas manuales y medición',
            'Dewalt'  => 'Herramientas eléctricas industriales',
            'Bosch'   => 'Herramientas eléctricas y accesorios',
            'Corona'  => 'Pinturas, grifería y acabados',
            'Pintuco' => 'Pinturas arquitectónicas e industriales',
            'Argos'   => 'Cementos y materiales de construcción',
            'Durman'  => 'Tuberías y accesorios hidrosanitarios',
            '3M'      => 'Abrasivos, cintas y seguridad industrial',
            'Pavco'   => 'Sistemas de tuberías y conexiones',
            'Sika'    => 'Impermeabilizantes, aditivos y sellantes',
            'Genérica'=> 'Materiales e insumos estándar',
        ];
        $marcaModels = [];
        foreach ($marcas as $nom => $desc) {
            $marcaModels[$nom] = Marca::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nombre' => $nom],
                ['descripcion' => $desc, 'activo' => true]
            );
        }

        // 3. Categorías
        $categorias = [
            'FIJ' => ['nombre' => 'Fijaciones y Tornillería', 'desc' => 'Tornillos, tuercas, anclajes y remaches'],
            'HER_MAN' => ['nombre' => 'Herramientas Manuales', 'desc' => 'Martillos, llaves, alicates, destornilladores y corte'],
            'HER_ELE' => ['nombre' => 'Herramientas Eléctricas', 'desc' => 'Taladros, pulidoras, sierras y compresores'],
            'TUB' => ['nombre' => 'Tuberías y Conexiones PVC', 'desc' => 'Tuberías de presión, sanitarias, ventilación y accesorios'],
            'PIN' => ['nombre' => 'Pinturas y Acabados', 'desc' => 'Vinilos, esmaltes, anticorrosivos, selladores y solventes'],
            'ELE' => ['nombre' => 'Eléctrica y Cables', 'desc' => 'Conductores eléctricos, tomas, interruptores y breakers'],
            'CEM' => ['nombre' => 'Cemento y Construcción', 'desc' => 'Cementos, adhesivos cerámicos, varillas y mallas'],
            'SEG' => ['nombre' => 'Seguridad Industrial', 'desc' => 'Protección personal, cascos, guantes y gafas'],
            'PLO' => ['nombre' => 'Plomería y Grifería', 'desc' => 'Válvulas, llaves de paso, sifones y teflón'],
            'ADH' => ['nombre' => 'Adhesivos y Sellantes', 'desc' => 'Siliconas, pegantes PVC, masillas y cintas'],
        ];
        $catModels = [];
        foreach ($categorias as $key => $data) {
            $catModels[$key] = Categoria::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nombre' => $data['nombre']],
                ['descripcion' => $data['desc'], 'activo' => true]
            );
        }

        // 4. Catálogo de 65 Productos
        $catalogo = [
            // --- FIJACIONES Y TORNILLERÍA (10) ---
            [
                'codigo' => 'FIJ-001',
                'codigo_barras' => '7701001000018',
                'nombre' => 'Tornillo Autorroscante Zincado 1" x 100 und',
                'cat' => 'FIJ', 'marca' => 'Genérica', 'und' => 'CAJ',
                'compra' => 2800, 'venta' => 5900, 'mayorista' => 4800, 'iva' => 19,
                'min' => 15, 'stock_base' => 60,
            ],
            [
                'codigo' => 'FIJ-002',
                'codigo_barras' => '7701001000025',
                'nombre' => 'Tornillo Drywall Punta Broca 3-1/2" x 100 und',
                'cat' => 'FIJ', 'marca' => 'Genérica', 'und' => 'CAJ',
                'compra' => 3200, 'venta' => 6500, 'mayorista' => 5200, 'iva' => 19,
                'min' => 10, 'stock_base' => 45,
            ],
            [
                'codigo' => 'FIJ-003',
                'codigo_barras' => '7701001000032',
                'nombre' => 'Puntilla de Acero sin Cabeza 1-1/2" x Libra',
                'cat' => 'FIJ', 'marca' => 'Genérica', 'und' => 'KG',
                'compra' => 2500, 'venta' => 4800, 'mayorista' => 3900, 'iva' => 19,
                'min' => 20, 'stock_base' => 80,
            ],
            [
                'codigo' => 'FIJ-004',
                'codigo_barras' => '7701001000049',
                'nombre' => 'Chazo Anclaje Expansivo 3/8" x 2-1/2" (Pack x 8)',
                'cat' => 'FIJ', 'marca' => 'Genérica', 'und' => 'PAQ',
                'compra' => 1800, 'venta' => 3800, 'mayorista' => 3000, 'iva' => 19,
                'min' => 15, 'stock_base' => 50,
            ],
            [
                'codigo' => 'FIJ-005',
                'codigo_barras' => '7701001000056',
                'nombre' => 'Tornillo Goloso Ensamble Madera 2" (Caja x 50)',
                'cat' => 'FIJ', 'marca' => 'Genérica', 'und' => 'CAJ',
                'compra' => 3500, 'venta' => 7200, 'mayorista' => 5800, 'iva' => 19,
                'min' => 10, 'stock_base' => 40,
            ],
            [
                'codigo' => 'FIJ-006',
                'codigo_barras' => '7701001000063',
                'nombre' => 'Remache Pop Aluminio 3/16" x 1/2" (Caja x 100)',
                'cat' => 'FIJ', 'marca' => 'Genérica', 'und' => 'CAJ',
                'compra' => 4200, 'venta' => 8500, 'mayorista' => 6900, 'iva' => 19,
                'min' => 8, 'stock_base' => 35,
            ],
            [
                'codigo' => 'FIJ-007',
                'codigo_barras' => '7701001000070',
                'nombre' => 'Perno Hexagonal Grado 2 de 3/8" x 1-1/2" Galvanizado',
                'cat' => 'FIJ', 'marca' => 'Genérica', 'und' => 'UND',
                'compra' => 800, 'venta' => 1800, 'mayorista' => 1400, 'iva' => 19,
                'min' => 50, 'stock_base' => 150,
            ],
            [
                'codigo' => 'FIJ-008',
                'codigo_barras' => '7701001000087',
                'nombre' => 'Tuerca Hexagonal Galvanizada 3/8" (Bolsa x 50)',
                'cat' => 'FIJ', 'marca' => 'Genérica', 'und' => 'PAQ',
                'compra' => 1500, 'venta' => 3200, 'mayorista' => 2500, 'iva' => 19,
                'min' => 15, 'stock_base' => 60,
            ],
            [
                'codigo' => 'FIJ-009',
                'codigo_barras' => '7701001000094',
                'nombre' => 'Arandela Plana Galvanizada 3/8" (Bolsa x 50)',
                'cat' => 'FIJ', 'marca' => 'Genérica', 'und' => 'PAQ',
                'compra' => 1200, 'venta' => 2600, 'mayorista' => 2000, 'iva' => 19,
                'min' => 15, 'stock_base' => 55,
            ],
            [
                'codigo' => 'FIJ-010',
                'codigo_barras' => '7701001000100',
                'nombre' => 'Varilla Roscada Galvanizada 3/8" x 1 Metro',
                'cat' => 'FIJ', 'marca' => 'Genérica', 'und' => 'UND',
                'compra' => 4500, 'venta' => 9200, 'mayorista' => 7500, 'iva' => 19,
                'min' => 10, 'stock_base' => 30,
            ],

            // --- HERRAMIENTAS MANUALES (10) ---
            [
                'codigo' => 'HER-001',
                'codigo_barras' => '7702002000017',
                'nombre' => 'Martillo de Uña Curva Stanley 20oz Mango Antivibración',
                'cat' => 'HER_MAN', 'marca' => 'Stanley', 'und' => 'UND',
                'compra' => 28000, 'venta' => 52000, 'mayorista' => 44000, 'iva' => 19,
                'min' => 4, 'stock_base' => 18,
            ],
            [
                'codigo' => 'HER-002',
                'codigo_barras' => '7702002000024',
                'nombre' => 'Destornillador Phillips #2 x 4" Stanley CushionGrip',
                'cat' => 'HER_MAN', 'marca' => 'Stanley', 'und' => 'UND',
                'compra' => 8500, 'venta' => 17500, 'mayorista' => 14000, 'iva' => 19,
                'min' => 6, 'stock_base' => 24,
            ],
            [
                'codigo' => 'HER-003',
                'codigo_barras' => '7702002000031',
                'nombre' => 'Llave Ajustable / Expansiva 12" Stanley Acero Cromo',
                'cat' => 'HER_MAN', 'marca' => 'Stanley', 'und' => 'UND',
                'compra' => 32000, 'venta' => 68000, 'mayorista' => 56000, 'iva' => 19,
                'min' => 3, 'stock_base' => 12,
            ],
            [
                'codigo' => 'HER-004',
                'codigo_barras' => '7702002000048',
                'nombre' => 'Alicate Universal de Electricista 8" Stanley Aislado 1000V',
                'cat' => 'HER_MAN', 'marca' => 'Stanley', 'und' => 'UND',
                'compra' => 22000, 'venta' => 46000, 'mayorista' => 38000, 'iva' => 19,
                'min' => 5, 'stock_base' => 16,
            ],
            [
                'codigo' => 'HER-005',
                'codigo_barras' => '7702002000055',
                'nombre' => 'Nivel Tubular de Aluminio 24" 3 Gotas Stanley',
                'cat' => 'HER_MAN', 'marca' => 'Stanley', 'und' => 'UND',
                'compra' => 22000, 'venta' => 45000, 'mayorista' => 37000, 'iva' => 19,
                'min' => 3, 'stock_base' => 10,
            ],
            [
                'codigo' => 'HER-006',
                'codigo_barras' => '7702002000062',
                'nombre' => 'Cinta Métrica Stanley PowerLock 8m / 26ft Hoja Ancha',
                'cat' => 'HER_MAN', 'marca' => 'Stanley', 'und' => 'UND',
                'compra' => 24000, 'venta' => 48000, 'mayorista' => 39000, 'iva' => 19,
                'min' => 6, 'stock_base' => 28,
            ],
            [
                'codigo' => 'HER-007',
                'codigo_barras' => '7702002000079',
                'nombre' => 'Serrucho Profesional de Carpintero 20" Stanley Dientes Templados',
                'cat' => 'HER_MAN', 'marca' => 'Stanley', 'und' => 'UND',
                'compra' => 25000, 'venta' => 52000, 'mayorista' => 42000, 'iva' => 19,
                'min' => 3, 'stock_base' => 11,
            ],
            [
                'codigo' => 'HER-008',
                'codigo_barras' => '7702002000086',
                'nombre' => 'Hombre Solo / Alicate de Presión Mordaza Curva 10" Stanley',
                'cat' => 'HER_MAN', 'marca' => 'Stanley', 'und' => 'UND',
                'compra' => 26000, 'venta' => 54000, 'mayorista' => 43000, 'iva' => 19,
                'min' => 4, 'stock_base' => 14,
            ],
            [
                'codigo' => 'HER-009',
                'codigo_barras' => '7702002000093',
                'nombre' => 'Juego de Llaves Bristol / Allen Hexagonales 9 Piezas',
                'cat' => 'HER_MAN', 'marca' => 'Stanley', 'und' => 'UND',
                'compra' => 15000, 'venta' => 32000, 'mayorista' => 26000, 'iva' => 19,
                'min' => 5, 'stock_base' => 20,
            ],
            [
                'codigo' => 'HER-010',
                'codigo_barras' => '7702002000109',
                'nombre' => 'Marco para Segueta 12" de Alta Tensión con Hoja Bimetálica',
                'cat' => 'HER_MAN', 'marca' => 'Stanley', 'und' => 'UND',
                'compra' => 14000, 'venta' => 29000, 'mayorista' => 23000, 'iva' => 19,
                'min' => 5, 'stock_base' => 15,
            ],

            // --- HERRAMIENTAS ELÉCTRICAS (7) ---
            [
                'codigo' => 'ELE-001',
                'codigo_barras' => '7703003000016',
                'nombre' => 'Taladro Percutor Dewalt 1/2" 710W Mandril Con Llave (DWD024)',
                'cat' => 'HER_ELE', 'marca' => 'Dewalt', 'und' => 'UND',
                'compra' => 265000, 'venta' => 495000, 'mayorista' => 420000, 'iva' => 19,
                'min' => 2, 'stock_base' => 8,
            ],
            [
                'codigo' => 'ELE-002',
                'codigo_barras' => '7703003000023',
                'nombre' => 'Pulidora Angular Bosch 4-1/2" 750W GWS 700 Profesional',
                'cat' => 'HER_ELE', 'marca' => 'Bosch', 'und' => 'UND',
                'compra' => 185000, 'venta' => 345000, 'mayorista' => 295000, 'iva' => 19,
                'min' => 2, 'stock_base' => 9,
            ],
            [
                'codigo' => 'ELE-003',
                'codigo_barras' => '7703003000030',
                'nombre' => 'Sierra Circular Dewalt 7-1/4" 1400W DWE560 con Disco Carburo',
                'cat' => 'HER_ELE', 'marca' => 'Dewalt', 'und' => 'UND',
                'compra' => 420000, 'venta' => 765000, 'mayorista' => 660000, 'iva' => 19,
                'min' => 1, 'stock_base' => 5,
            ],
            [
                'codigo' => 'ELE-004',
                'codigo_barras' => '7703003000047',
                'nombre' => 'Atornillador Inalámbrico Dewalt 12V Max Ion-Litio con 2 Baterías',
                'cat' => 'HER_ELE', 'marca' => 'Dewalt', 'und' => 'UND',
                'compra' => 295000, 'venta' => 540000, 'mayorista' => 460000, 'iva' => 19,
                'min' => 2, 'stock_base' => 6,
            ],
            [
                'codigo' => 'ELE-005',
                'codigo_barras' => '7703003000054',
                'nombre' => 'Lijadora Roto Orbital Bosch 5" 250W GEX 125-1 AE',
                'cat' => 'HER_ELE', 'marca' => 'Bosch', 'und' => 'UND',
                'compra' => 165000, 'venta' => 310000, 'mayorista' => 265000, 'iva' => 19,
                'min' => 2, 'stock_base' => 5,
            ],
            [
                'codigo' => 'ELE-006',
                'codigo_barras' => '7703003000061',
                'nombre' => 'Compresor de Aire Monofásico 25L 2HP con Filtro Regulador',
                'cat' => 'HER_ELE', 'marca' => 'Genérica', 'und' => 'UND',
                'compra' => 380000, 'venta' => 690000, 'mayorista' => 590000, 'iva' => 19,
                'min' => 1, 'stock_base' => 4,
            ],
            [
                'codigo' => 'ELE-007',
                'codigo_barras' => '7703003000078',
                'nombre' => 'Pistola de Calor Industrial 2000W 2 Temperaturas Bosch GHG 180',
                'cat' => 'HER_ELE', 'marca' => 'Bosch', 'und' => 'UND',
                'compra' => 110000, 'venta' => 215000, 'mayorista' => 180000, 'iva' => 19,
                'min' => 2, 'stock_base' => 6,
            ],

            // --- TUBERÍAS Y CONEXIONES PVC (8) ---
            [
                'codigo' => 'TUB-001',
                'codigo_barras' => '7704004000015',
                'nombre' => 'Tubo PVC Sanitario 4" x 3 Metros Durman Aguas Negras',
                'cat' => 'TUB', 'marca' => 'Durman', 'und' => 'UND',
                'compra' => 36000, 'venta' => 68000, 'mayorista' => 55000, 'iva' => 19,
                'min' => 5, 'stock_base' => 25,
            ],
            [
                'codigo' => 'TUB-002',
                'codigo_barras' => '7704004000022',
                'nombre' => 'Tubo PVC Presión RDE 21 de 1/2" x 6 Metros Pavco',
                'cat' => 'TUB', 'marca' => 'Pavco', 'und' => 'UND',
                'compra' => 13500, 'venta' => 27000, 'mayorista' => 21500, 'iva' => 19,
                'min' => 10, 'stock_base' => 45,
            ],
            [
                'codigo' => 'TUB-003',
                'codigo_barras' => '7704004000039',
                'nombre' => 'Codo PVC Presión 90 Grados 1/2" Soldar Pavco',
                'cat' => 'TUB', 'marca' => 'Pavco', 'und' => 'UND',
                'compra' => 750, 'venta' => 1900, 'mayorista' => 1350, 'iva' => 19,
                'min' => 30, 'stock_base' => 120,
            ],
            [
                'codigo' => 'TUB-004',
                'codigo_barras' => '7704004000046',
                'nombre' => 'Tee PVC Presión 1/2" Soldar Pavco',
                'cat' => 'TUB', 'marca' => 'Pavco', 'und' => 'UND',
                'compra' => 900, 'venta' => 2200, 'mayorista' => 1600, 'iva' => 19,
                'min' => 25, 'stock_base' => 90,
            ],
            [
                'codigo' => 'TUB-005',
                'codigo_barras' => '7704004000053',
                'nombre' => 'Adaptador Macho PVC Presión 1/2" Pavco',
                'cat' => 'TUB', 'marca' => 'Pavco', 'und' => 'UND',
                'compra' => 650, 'venta' => 1700, 'mayorista' => 1200, 'iva' => 19,
                'min' => 25, 'stock_base' => 85,
            ],
            [
                'codigo' => 'TUB-006',
                'codigo_barras' => '7704004000060',
                'nombre' => 'Unión PVC Presión 1/2" Pavco Soldar',
                'cat' => 'TUB', 'marca' => 'Pavco', 'und' => 'UND',
                'compra' => 600, 'venta' => 1500, 'mayorista' => 1100, 'iva' => 19,
                'min' => 30, 'stock_base' => 100,
            ],
            [
                'codigo' => 'TUB-007',
                'codigo_barras' => '7704004000077',
                'nombre' => 'Soldadura Líquida para PVC Transparente 1/8 Galón Durman',
                'cat' => 'TUB', 'marca' => 'Durman', 'und' => 'UND',
                'compra' => 8500, 'venta' => 17500, 'mayorista' => 14000, 'iva' => 19,
                'min' => 8, 'stock_base' => 30,
            ],
            [
                'codigo' => 'TUB-008',
                'codigo_barras' => '7704004000084',
                'nombre' => 'Limpiador Desengrasante Removedor PVC 1/8 Galón Pavco',
                'cat' => 'TUB', 'marca' => 'Pavco', 'und' => 'UND',
                'compra' => 7200, 'venta' => 15000, 'mayorista' => 12000, 'iva' => 19,
                'min' => 8, 'stock_base' => 28,
            ],

            // --- PINTURAS Y ACABADOS (7) ---
            [
                'codigo' => 'PIN-001',
                'codigo_barras' => '7705005000014',
                'nombre' => 'Vinilo Acrílico Interior Tipo 1 Pintuco Blanco Mate (Galón)',
                'cat' => 'PIN', 'marca' => 'Pintuco', 'und' => 'GL',
                'compra' => 45000, 'venta' => 88000, 'mayorista' => 74000, 'iva' => 19,
                'min' => 6, 'stock_base' => 24,
            ],
            [
                'codigo' => 'PIN-002',
                'codigo_barras' => '7705005000021',
                'nombre' => 'Esmalte Anticorrosivo Doméstico Pintuco Rojo Óxido (Galón)',
                'cat' => 'PIN', 'marca' => 'Pintuco', 'und' => 'GL',
                'compra' => 58000, 'venta' => 112000, 'mayorista' => 95000, 'iva' => 19,
                'min' => 4, 'stock_base' => 15,
            ],
            [
                'codigo' => 'PIN-003',
                'codigo_barras' => '7705005000038',
                'nombre' => 'Pintura Fachada Exterior Corona Blanco Hueso Impermeable (Galón)',
                'cat' => 'PIN', 'marca' => 'Corona', 'und' => 'GL',
                'compra' => 52000, 'venta' => 99000, 'mayorista' => 84000, 'iva' => 19,
                'min' => 4, 'stock_base' => 16,
            ],
            [
                'codigo' => 'PIN-004',
                'codigo_barras' => '7705005000045',
                'nombre' => 'Sellador y Fijador de Superficies Acrílico Pintuco (Galón)',
                'cat' => 'PIN', 'marca' => 'Pintuco', 'und' => 'GL',
                'compra' => 32000, 'venta' => 64000, 'mayorista' => 52000, 'iva' => 19,
                'min' => 3, 'stock_base' => 12,
            ],
            [
                'codigo' => 'PIN-005',
                'codigo_barras' => '7705005000052',
                'nombre' => 'Thinner / Disolvente Fino Industrial Limpieza (Galón)',
                'cat' => 'PIN', 'marca' => 'Genérica', 'und' => 'GL',
                'compra' => 18000, 'venta' => 36000, 'mayorista' => 29000, 'iva' => 19,
                'min' => 8, 'stock_base' => 32,
            ],
            [
                'codigo' => 'PIN-006',
                'codigo_barras' => '7705005000069',
                'nombre' => 'Brocha Profesional Cerda Mono 3" Mango Plástico',
                'cat' => 'PIN', 'marca' => 'Corona', 'und' => 'UND',
                'compra' => 5500, 'venta' => 12500, 'mayorista' => 9500, 'iva' => 19,
                'min' => 10, 'stock_base' => 40,
            ],
            [
                'codigo' => 'PIN-007',
                'codigo_barras' => '7705005000076',
                'nombre' => 'Rodillo Antigoteo Felpa 9" con Maneral Metálico Corona',
                'cat' => 'PIN', 'marca' => 'Corona', 'und' => 'UND',
                'compra' => 8500, 'venta' => 18000, 'mayorista' => 14500, 'iva' => 19,
                'min' => 8, 'stock_base' => 30,
            ],

            // --- ELÉCTRICA Y CABLES (6) ---
            [
                'codigo' => 'CAB-001',
                'codigo_barras' => '7706006000013',
                'nombre' => 'Cable Eléctrico THHN Cobre 7 Hilos Calibre 12 AWG Negro (Metro)',
                'cat' => 'ELE', 'marca' => 'Genérica', 'und' => 'MT',
                'compra' => 3100, 'venta' => 6400, 'mayorista' => 5100, 'iva' => 19,
                'min' => 100, 'stock_base' => 400,
            ],
            [
                'codigo' => 'CAB-002',
                'codigo_barras' => '7706006000020',
                'nombre' => 'Cable Dúplex Polarizado SPT Calibre 2x14 AWG Blanco (Metro)',
                'cat' => 'ELE', 'marca' => 'Genérica', 'und' => 'MT',
                'compra' => 2400, 'venta' => 5100, 'mayorista' => 4000, 'iva' => 19,
                'min' => 80, 'stock_base' => 300,
            ],
            [
                'codigo' => 'CAB-003',
                'codigo_barras' => '7706006000037',
                'nombre' => 'Tomacorriente Doble con Polo a Tierra 15A 125V Blanco',
                'cat' => 'ELE', 'marca' => 'Genérica', 'und' => 'UND',
                'compra' => 7800, 'venta' => 16500, 'mayorista' => 13200, 'iva' => 19,
                'min' => 15, 'stock_base' => 60,
            ],
            [
                'codigo' => 'CAB-004',
                'codigo_barras' => '7706006000044',
                'nombre' => 'Interruptor Sencillo 1 Vía 15A 125V Empotrar Blanco',
                'cat' => 'ELE', 'marca' => 'Genérica', 'und' => 'UND',
                'compra' => 6500, 'venta' => 14000, 'mayorista' => 11000, 'iva' => 19,
                'min' => 15, 'stock_base' => 55,
            ],
            [
                'codigo' => 'CAB-005',
                'codigo_barras' => '7706006000051',
                'nombre' => 'Cinta Aislante de Vinilo Scotch 3M Negra 19mm x 18m Profesional',
                'cat' => 'ELE', 'marca' => '3M', 'und' => 'RLL',
                'compra' => 4200, 'venta' => 8900, 'mayorista' => 7000, 'iva' => 19,
                'min' => 20, 'stock_base' => 75,
            ],
            [
                'codigo' => 'CAB-006',
                'codigo_barras' => '7706006000068',
                'nombre' => 'Disyuntor Termomagnético Breaker Enchufable 1 Polo x 20A',
                'cat' => 'ELE', 'marca' => 'Genérica', 'und' => 'UND',
                'compra' => 16000, 'venta' => 34000, 'mayorista' => 27000, 'iva' => 19,
                'min' => 8, 'stock_base' => 25,
            ],

            // --- CEMENTO Y CONSTRUCCIÓN (6) ---
            [
                'codigo' => 'CEM-001',
                'codigo_barras' => '7707007000012',
                'nombre' => 'Cemento Gris Argos Uso General Estructural (Bulto x 50kg)',
                'cat' => 'CEM', 'marca' => 'Argos', 'und' => 'BTO',
                'compra' => 29500, 'venta' => 44000, 'mayorista' => 38000, 'iva' => 0,
                'min' => 20, 'stock_base' => 80,
            ],
            [
                'codigo' => 'CEM-002',
                'codigo_barras' => '7707007000029',
                'nombre' => 'Pegante Cerámico Piso sobre Piso Corona Gris (Bulto x 25kg)',
                'cat' => 'CEM', 'marca' => 'Corona', 'und' => 'BTO',
                'compra' => 24000, 'venta' => 39500, 'mayorista' => 33500, 'iva' => 0,
                'min' => 10, 'stock_base' => 40,
            ],
            [
                'codigo' => 'CEM-003',
                'codigo_barras' => '7707007000036',
                'nombre' => 'Estuco Listo Interior Corona Acabado Liso Blanco (Bulto x 25kg)',
                'cat' => 'CEM', 'marca' => 'Corona', 'und' => 'BTO',
                'compra' => 19000, 'venta' => 33000, 'mayorista' => 27500, 'iva' => 0,
                'min' => 10, 'stock_base' => 35,
            ],
            [
                'codigo' => 'CEM-004',
                'codigo_barras' => '7707007000043',
                'nombre' => 'Cal Hidratada Alta Pureza para Obra y Albañilería (Bulto x 20kg)',
                'cat' => 'CEM', 'marca' => 'Genérica', 'und' => 'BTO',
                'compra' => 9500, 'venta' => 17000, 'mayorista' => 14000, 'iva' => 0,
                'min' => 8, 'stock_base' => 30,
            ],
            [
                'codigo' => 'CEM-005',
                'codigo_barras' => '7707007000050',
                'nombre' => 'Varilla Corrugada Grado 60 Sismoresistente 1/2" x 6 Metros',
                'cat' => 'CEM', 'marca' => 'Genérica', 'und' => 'UND',
                'compra' => 37000, 'venta' => 64000, 'mayorista' => 53000, 'iva' => 19,
                'min' => 15, 'stock_base' => 60,
            ],
            [
                'codigo' => 'CEM-006',
                'codigo_barras' => '7707007000067',
                'nombre' => 'Alambre Negro Recocido para Amarre Calibre 18 (Kilo)',
                'cat' => 'CEM', 'marca' => 'Genérica', 'und' => 'KG',
                'compra' => 4800, 'venta' => 9500, 'mayorista' => 7800, 'iva' => 19,
                'min' => 25, 'stock_base' => 100,
            ],

            // --- PLOMERÍA Y GRIFERÍA (5) ---
            [
                'codigo' => 'PLO-001',
                'codigo_barras' => '7708008000011',
                'nombre' => 'Válvula / Llave de Paso Esfera Bola Roscada Latón 1/2" NPT',
                'cat' => 'PLO', 'marca' => 'Corona', 'und' => 'UND',
                'compra' => 13500, 'venta' => 28500, 'mayorista' => 23000, 'iva' => 19,
                'min' => 6, 'stock_base' => 25,
            ],
            [
                'codigo' => 'PLO-002',
                'codigo_barras' => '7708008000028',
                'nombre' => 'Cinta Teflón Sellante Roscas 3/4" x 12 Metros Durman Profesional',
                'cat' => 'PLO', 'marca' => 'Durman', 'und' => 'RLL',
                'compra' => 2100, 'venta' => 5200, 'mayorista' => 4000, 'iva' => 19,
                'min' => 20, 'stock_base' => 80,
            ],
            [
                'codigo' => 'PLO-003',
                'codigo_barras' => '7708008000035',
                'nombre' => 'Sifón Tipo Botella para Lavamanos / Lavaplatos Flexible PVC',
                'cat' => 'PLO', 'marca' => 'Corona', 'und' => 'UND',
                'compra' => 8500, 'venta' => 18500, 'mayorista' => 14500, 'iva' => 19,
                'min' => 5, 'stock_base' => 22,
            ],
            [
                'codigo' => 'PLO-004',
                'codigo_barras' => '7708008000042',
                'nombre' => 'Válvula de Retención / Check Horizontal Latón 1/2"',
                'cat' => 'PLO', 'marca' => 'Genérica', 'und' => 'UND',
                'compra' => 19000, 'venta' => 39500, 'mayorista' => 32000, 'iva' => 19,
                'min' => 4, 'stock_base' => 15,
            ],
            [
                'codigo' => 'PLO-005',
                'codigo_barras' => '7708008000059',
                'nombre' => 'Grifo / Llave Terminal para Jardín y Manguera Bronce 1/2"',
                'cat' => 'PLO', 'marca' => 'Corona', 'und' => 'UND',
                'compra' => 11500, 'venta' => 24000, 'mayorista' => 19000, 'iva' => 19,
                'min' => 6, 'stock_base' => 20,
            ],

            // --- SEGURIDAD INDUSTRIAL (3) ---
            [
                'codigo' => 'SEG-001',
                'codigo_barras' => '7709009000010',
                'nombre' => 'Casco de Seguridad Industrial Tipo 1 Clase E con Tafilete Ratchet Blanco',
                'cat' => 'SEG', 'marca' => '3M', 'und' => 'UND',
                'compra' => 17500, 'venta' => 36000, 'mayorista' => 29000, 'iva' => 19,
                'min' => 5, 'stock_base' => 20,
            ],
            [
                'codigo' => 'SEG-002',
                'codigo_barras' => '7709009000027',
                'nombre' => 'Gafas de Seguridad Policarbonato Anti-Ralladura / Anti-Empañante 3M',
                'cat' => 'SEG', 'marca' => '3M', 'und' => 'UND',
                'compra' => 6800, 'venta' => 15500, 'mayorista' => 12000, 'iva' => 19,
                'min' => 10, 'stock_base' => 35,
            ],
            [
                'codigo' => 'SEG-003',
                'codigo_barras' => '7709009000034',
                'nombre' => 'Guantes de Carnaza Reforzados Tipo Ingeniero / Soldador (Par)',
                'cat' => 'SEG', 'marca' => 'Genérica', 'und' => 'PAR',
                'compra' => 11000, 'venta' => 24500, 'mayorista' => 19000, 'iva' => 19,
                'min' => 8, 'stock_base' => 30,
            ],

            // --- ADHESIVOS Y SELLANTES (3) ---
            [
                'codigo' => 'ADH-001',
                'codigo_barras' => '7709509500018',
                'nombre' => 'Sikaflex 11FC Poliuretano Adhesivo Sellador Elástico Gris 300ml',
                'cat' => 'ADH', 'marca' => 'Sika', 'und' => 'UND',
                'compra' => 23000, 'venta' => 45000, 'mayorista' => 37000, 'iva' => 19,
                'min' => 6, 'stock_base' => 26,
            ],
            [
                'codigo' => 'ADH-002',
                'codigo_barras' => '7709509500025',
                'nombre' => 'Silicona Acética Universal Transparente Anti-Hongos 280ml',
                'cat' => 'ADH', 'marca' => 'Sika', 'und' => 'UND',
                'compra' => 9500, 'venta' => 19800, 'mayorista' => 16000, 'iva' => 19,
                'min' => 8, 'stock_base' => 35,
            ],
            [
                'codigo' => 'ADH-003',
                'codigo_barras' => '7709509500032',
                'nombre' => 'SikaTop 107 Mortero Impermeabilizante Bicomponente (Juego x 4.5kg)',
                'cat' => 'ADH', 'marca' => 'Sika', 'und' => 'UND',
                'compra' => 34000, 'venta' => 65000, 'mayorista' => 54000, 'iva' => 19,
                'min' => 4, 'stock_base' => 16,
            ],
        ];

        foreach ($catalogo as $pData) {
            $catId = $catModels[$pData['cat']]->id;
            $marcaId = $marcaModels[$pData['marca']]->id;
            $undId = $undModels[$pData['und']]->id;

            // Total stock consolidado
            $totalStock = $pData['stock_base'];

            $producto = Producto::firstOrCreate(
                [
                    'empresa_id' => $empresa->id,
                    'codigo' => $pData['codigo'],
                ],
                [
                    'categoria_id' => $catId,
                    'marca_id' => $marcaId,
                    'unidad_medida_id' => $undId,
                    'codigo_barras' => $pData['codigo_barras'],
                    'nombre' => $pData['nombre'],
                    'descripcion' => $pData['nombre'] . ' de alta calidad para ferretería y construcción.',
                    'precio_compra' => $pData['compra'],
                    'precio_venta' => $pData['venta'],
                    'precio_mayorista' => $pData['mayorista'],
                    'precio_distribuidor' => $pData['compra'] * 1.15,
                    'stock' => $totalStock,
                    'stock_minimo' => $pData['min'],
                    'iva' => $pData['iva'],
                    'estado' => EstadoGeneral::ACTIVO,
                ]
            );

            // Poblar inventario en cada sucursal
            foreach ($sucursales as $idx => $suc) {
                // Dividir el stock: ~65% sede principal, ~35% sede secundaria
                $stockSede = $idx === 0
                    ? round($totalStock * 0.65)
                    : round($totalStock * 0.35);

                $inv = Inventario::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'sucursal_id' => $suc->id,
                        'producto_id' => $producto->id,
                    ],
                    [
                        'stock' => $stockSede,
                        'stock_minimo' => max(1, round($pData['min'] / 2)),
                        'ubicacion' => $idx === 0 ? 'Bodega Principal - Pasillo F' : 'Mostrador Norte - Estante 2',
                    ]
                );

                // Movimiento inicial de kardex
                MovimientoInventario::firstOrCreate(
                    [
                        'empresa_id' => $empresa->id,
                        'sucursal_id' => $suc->id,
                        'producto_id' => $producto->id,
                        'tipo' => TipoMovimientoInventario::ENTRADA_COMPRA->value,
                    ],
                    [
                        'user_id' => $adminUser->id,
                        'cantidad' => $stockSede,
                        'costo_unitario' => $pData['compra'],
                        'stock_anterior' => 0,
                        'stock_posterior' => $stockSede,
                        'referencia' => 'Apertura de Inventario Inicial Ferretería Demo',
                        'notas' => 'Inventario base importado en sistema POS.',
                    ]
                );
            }
        }
    }
}
