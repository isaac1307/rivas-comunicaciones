<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega el rol "cobrador" al catálogo de roles de usuario.
     */
    public function up(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->enum('rol', ['administrador', 'operador', 'cobrador'])->default('operador')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->enum('rol', ['administrador', 'operador'])->default('administrador')->change();
        });
    }
};
