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
        Schema::create('reservaciones', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->unique();
            $table->string('nombre_solicitante', 200);
            $table->string('telefono', 15);
            $table->string('direccion', 300);
            $table->string('correo_electronico', 150)->nullable();
            $table->enum('tipo_servicio', ['instalacion_nueva', 'visita_tecnica', 'atencion_personalizada']);
            $table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();
            $table->date('fecha_solicitada')->nullable();
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['pendiente', 'programada', 'completada', 'cancelada'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservaciones');
    }
};
