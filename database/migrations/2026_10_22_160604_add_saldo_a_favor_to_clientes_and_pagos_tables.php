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
        Schema::table('clientes', function (Blueprint $table) {
            $table->decimal('saldo_a_favor', 10, 2)->default(0.00)->after('estado');
        });

        Schema::table('pagos', function (Blueprint $table) {
            $table->decimal('monto_saldo_favor', 10, 2)->default(0.00)->after('monto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn('monto_saldo_favor');
        });

        Schema::table('clientes', function (Blueprint $table) {
            $table->dropColumn('saldo_a_favor');
        });
    }
};
