<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->string('client_transaction_id', 100)->nullable()->after('numero_venta');
            $table->boolean('sincronizada_offline')->default(false)->after('client_transaction_id');

            $table->index(['empresa_id', 'client_transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropIndex(['empresa_id', 'client_transaction_id']);
            $table->dropColumn(['client_transaction_id', 'sincronizada_offline']);
        });
    }
};
