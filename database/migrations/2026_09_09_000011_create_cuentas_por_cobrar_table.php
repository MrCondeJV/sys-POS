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
        Schema::create('cuentas_por_cobrar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->nullable()->constrained('sucursales')->nullOnDelete();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();

            $table->string('numero_documento', 50);
            $table->string('concepto', 255);

            $table->decimal('monto_total', 14, 2);
            $table->decimal('monto_pagado', 14, 2)->default(0);
            $table->decimal('saldo_pendiente', 14, 2);

            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');

            $table->string('estado', 20)->default('PENDIENTE'); // PENDIENTE, PARCIAL, PAGADA, ANULADA
            $table->text('observaciones')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'numero_documento']);
            $table->index(['empresa_id', 'cliente_id']);
            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'fecha_vencimiento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuentas_por_cobrar');
    }
};
