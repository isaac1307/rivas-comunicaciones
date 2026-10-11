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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 200);
            $table->string('direccion', 300);
            $table->string('telefono', 15);
            $table->string('correo_electronico', 150)->nullable();
            $table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();
            $table->date('fecha_alta')->useCurrent();
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
