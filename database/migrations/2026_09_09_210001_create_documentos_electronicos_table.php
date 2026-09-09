<?php

use App\Enums\EstadoDian;
use App\Enums\TipoDocumentoElectronico;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos_electronicos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('documento_venta_id')->constrained('documentos_venta')->cascadeOnDelete();
            $table->foreignId('resolucion_id')->nullable()->constrained('resoluciones_facturacion')->nullOnDelete();
            $table->string('tipo', 40)->default(TipoDocumentoElectronico::FACTURA_ELECTRONICA->value);
            $table->string('cufe', 255)->nullable()->unique();
            $table->string('prefijo', 10);
            $table->unsignedBigInteger('numero');
            $table->string('consecutivo_completo', 50);
            $table->string('estado_dian', 30)->default(EstadoDian::PENDIENTE->value);
            $table->string('codigo_respuesta_dian', 50)->nullable();
            $table->text('mensaje_dian')->nullable();
            $table->string('xml_path', 255)->nullable();
            $table->string('pdf_path', 255)->nullable();
            $table->text('qr_data')->nullable();
            $table->json('metadatos_envio')->nullable();
            $table->json('metadatos_respuesta')->nullable();
            $table->dateTime('fecha_emision');
            $table->dateTime('fecha_validacion')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'estado_dian']);
            $table->index(['empresa_id', 'consecutivo_completo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos_electronicos');
    }
};
