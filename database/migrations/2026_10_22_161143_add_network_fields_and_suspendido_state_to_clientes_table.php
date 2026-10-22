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
            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE clientes MODIFY COLUMN estado ENUM('activo', 'suspendido', 'inactivo') DEFAULT 'activo'");
            } else {
                $table->string('estado', 20)->default('activo')->change();
            }

            $table->string('serie_ont', 100)->nullable()->index()->after('notas');
            $table->string('usuario_pppoe', 100)->nullable()->index()->after('serie_ont');
            $table->string('coordenadas_gps', 100)->nullable()->after('usuario_pppoe');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropIndex(['serie_ont']);
            $table->dropIndex(['usuario_pppoe']);
            $table->dropColumn(['serie_ont', 'usuario_pppoe', 'coordenadas_gps']);

            if (DB::getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE clientes MODIFY COLUMN estado ENUM('activo', 'inactivo') DEFAULT 'activo'");
            } else {
                $table->string('estado', 20)->default('activo')->change();
            }
        });
    }
};
