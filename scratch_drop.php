<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;

Schema::dropIfExists('producto_lotes');
if (Schema::hasColumn('productos', 'laboratorio_id')) {
    Schema::table('productos', function ($t) {
        $t->dropForeign(['laboratorio_id']);
        $t->dropForeign(['principio_activo_id']);
        $t->dropColumn(['laboratorio_id', 'principio_activo_id', 'registro_sanitario', 'requiere_receta', 'maneja_lotes']);
    });
}
Schema::dropIfExists('principios_activos');
Schema::dropIfExists('laboratorios');

echo "Cleaned partial tables successfully.\n";
