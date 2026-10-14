<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Distribución de cada pago entre los cargos que liquida (un pago puede cubrir varios meses).
     */
    public function up(): void
    {
        Schema::create('aplicaciones_pago', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained('pagos')->cascadeOnDelete();
            $table->foreignId('cargo_id')->constrained('cargos')->restrictOnDelete();
            $table->decimal('monto_aplicado', 10, 2);
            $table->timestamps();

            $table->unique(['pago_id', 'cargo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aplicaciones_pago');
    }
};
