<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Planes comerciales SaaS
        Schema::create('planes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('slug', 100)->unique();
            $table->text('descripcion')->nullable();
            $table->decimal('precio_mensual', 12, 2)->default(0);
            $table->decimal('precio_anual', 12, 2)->default(0);
            $table->integer('limite_sucursales')->default(1); // 0 o 9999 = ilimitado
            $table->integer('limite_usuarios')->default(2);     // 0 o 9999 = ilimitado
            $table->boolean('permite_facturacion_electronica')->default(false);
            $table->boolean('permite_api')->default(false);
            $table->boolean('permite_farmacia')->default(false);
            $table->boolean('permite_ferreteria')->default(false);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Suscripciones de empresas
        Schema::create('suscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('planes')->restrictOnDelete();
            $table->string('estado', 30)->default('ACTIVA'); // ACTIVA, PRUEBA, SUSPENDIDA, CANCELADA, VENCIDA
            $table->dateTime('fecha_inicio');
            $table->dateTime('fecha_fin')->nullable();
            $table->string('ciclo_facturacion', 20)->default('MENSUAL'); // MENSUAL, ANUAL
            $table->decimal('precio_pago', 12, 2)->default(0);
            $table->string('metodo_pago', 50)->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('suscripciones');
        Schema::dropIfExists('planes');
    }
};
