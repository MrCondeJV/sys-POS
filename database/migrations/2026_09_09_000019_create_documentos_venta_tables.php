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
        Schema::create('documentos_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('sucursal_id')->constrained('sucursales')->cascadeOnDelete();
            $table->foreignId('venta_id')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('tipo', 30); // TICKET, DOCUMENTO_EQUIVALENTE, FACTURA
            $table->string('estado', 30)->default('EMITIDO'); // BORRADOR, EMITIDO, ANULADO
            
            $table->string('prefijo', 10)->nullable();
            $table->unsignedBigInteger('numero');
            $table->string('numero_completo', 50)->index();
            
            $table->dateTime('fecha_emision');
            $table->decimal('subtotal', 14, 2);
            $table->decimal('impuesto_total', 14, 2)->default(0);
            $table->decimal('descuento_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2);
            
            $table->text('observaciones')->nullable();
            $table->json('metadatos')->nullable(); // Para resoluciones DIAN, CUFE, QR en fases posteriores
            
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'tipo', 'numero']);
            $table->index(['empresa_id', 'estado']);
            $table->index(['empresa_id', 'fecha_emision']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_venta');
    }
};
