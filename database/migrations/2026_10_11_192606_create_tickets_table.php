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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->unique();
            $table->foreignId('cliente_id')->constrained('clientes')->cascadeOnDelete();
            $table->enum('categoria', ['falla_servicio', 'soporte_tecnico', 'consulta', 'cambio_plan', 'cancelacion']);
            $table->text('descripcion');
            $table->enum('estado', ['abierto', 'en_proceso', 'resuelto', 'cerrado'])->default('abierto');
            $table->timestamp('fecha_resolucion')->nullable();
            $table->text('observaciones_admin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
