<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ampliación de la tabla planes
        Schema::table('planes', function (Blueprint $table) {
            $table->char('moneda', 3)->default('COP')->after('precio_anual');
            $table->unsignedSmallInteger('dias_prueba')->default(14)->after('moneda');
            $table->unsignedTinyInteger('orden_visual')->default(1)->after('dias_prueba');
        });

        // 2. Tabla pagos_suscripcion (historial de intenciones y transacciones Wompi)
        Schema::create('pagos_suscripcion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('suscripcion_id')->nullable()->constrained('suscripciones')->nullOnDelete();
            $table->foreignId('plan_id')->constrained('planes')->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ciclo_facturacion', 20)->default('MENSUAL'); // MENSUAL, ANUAL
            $table->string('reference', 80)->unique();
            $table->unsignedBigInteger('amount_in_cents');
            $table->char('currency', 3)->default('COP');
            $table->string('integrity_signature', 64);
            $table->string('wompi_transaction_id', 64)->nullable()->unique();
            $table->string('status', 24)->default('PENDING')->index(); // PENDING, APPROVED, DECLINED, VOIDED, ERROR, AMOUNT_MISMATCH, EXPIRED
            $table->string('payment_method_type', 40)->nullable();
            $table->string('status_message', 255)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at');
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'status']);
        });

        // 3. Ampliación de la tabla suscripciones
        Schema::table('suscripciones', function (Blueprint $table) {
            $table->timestamp('fecha_gracia_fin')->nullable()->after('fecha_fin');
            $table->boolean('renovacion_automatica')->default(false)->after('fecha_gracia_fin');
            $table->foreignId('ultimo_pago_id')->nullable()->after('renovacion_automatica')->constrained('pagos_suscripcion')->nullOnDelete();
        });

        // 4. Tabla wompi_webhook_events (auditoría forense e idempotencia criptográfica)
        Schema::create('wompi_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 100)->unique();
            $table->string('event_type', 60)->index();
            $table->string('wompi_transaction_id', 64)->index();
            $table->string('reference', 80)->nullable()->index();
            $table->string('transaction_status', 24);
            $table->boolean('signature_valid');
            $table->timestamp('processed_at')->nullable();
            $table->string('processing_result', 40); // PROCESSED, DUPLICATE_IGNORED, INVALID_SIGNATURE, AMOUNT_MISMATCH, NOT_FOUND
            $table->string('ip_address', 45)->nullable();
            $table->json('payload');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wompi_webhook_events');

        Schema::table('suscripciones', function (Blueprint $table) {
            $table->dropForeign(['ultimo_pago_id']);
            $table->dropColumn(['fecha_gracia_fin', 'renovacion_automatica', 'ultimo_pago_id']);
        });

        Schema::dropIfExists('pagos_suscripcion');

        Schema::table('planes', function (Blueprint $table) {
            $table->dropColumn(['moneda', 'dias_prueba', 'orden_visual']);
        });
    }
};
