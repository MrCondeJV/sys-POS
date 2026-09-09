<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificaciones_sistema', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();

            $table->string('tipo', 50); // STOCK_BAJO, CAJA_CERRADA, etc.
            $table->string('nivel', 20)->default('INFO'); // INFO, WARNING, DANGER, SUCCESS
            $table->string('titulo', 255);
            $table->text('mensaje');
            $table->string('url_accion', 255)->nullable();

            $table->boolean('leida')->default(false);
            $table->timestamp('leida_at')->nullable();
            $table->json('datos')->nullable();

            $table->timestamps();

            $table->index(['empresa_id', 'leida', 'created_at']);
            $table->index(['empresa_id', 'user_id', 'leida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones_sistema');
    }
};
