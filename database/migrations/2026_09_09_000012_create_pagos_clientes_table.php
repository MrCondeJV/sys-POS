<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('cuenta_por_cobrar_id')->constrained('cuentas_por_cobrar')->cascadeOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();

            $table->string('numero_recibo', 50);
            $table->decimal('monto', 14, 2);
            $table->string('metodo_pago', 30)->default('EFECTIVO');
            $table->string('referencia_pago', 100)->nullable();
            $table->date('fecha_pago');

            $table->decimal('saldo_anterior', 14, 2);
            $table->decimal('saldo_posterior', 14, 2);

            $table->string('notas', 255)->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('estado', 20)->default('APLICADO'); // APLICADO, ANULADO

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'numero_recibo']);
            $table->index(['empresa_id', 'cuenta_por_cobrar_id']);
            $table->index(['empresa_id', 'cliente_id']);
            $table->index(['empresa_id', 'fecha_pago']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_clientes');
    }
};
