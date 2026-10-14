<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cargos (adeudos) generados a cada cliente: mensualidades, instalaciones, reconexiones, etc.
     */
    public function up(): void
    {
        Schema::create('cargos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('plan_id')->nullable()->constrained('planes')->nullOnDelete();
            $table->enum('tipo', ['mensualidad', 'instalacion', 'reconexion', 'otro'])->default('mensualidad');
            $table->string('periodo', 7); // Formato: YYYY-MM
            $table->string('concepto', 200);
            $table->decimal('monto', 10, 2);
            $table->decimal('monto_pagado', 10, 2)->default(0);
            $table->date('fecha_vencimiento');
            $table->enum('estado', ['pendiente', 'parcial', 'pagado', 'cancelado'])->default('pendiente');
            $table->timestamps();

            $table->index(['cliente_id', 'periodo']);
            $table->index(['estado', 'fecha_vencimiento']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cargos');
    }
};
