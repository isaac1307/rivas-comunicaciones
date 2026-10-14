<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pagos recibidos de los clientes (registrados por cobradores o administradores).
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->unique(); // Formato: PAG-YYYYMMDD-XXXX
            $table->foreignId('cliente_id')->constrained('clientes')->restrictOnDelete();
            $table->foreignId('usuario_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->decimal('monto', 10, 2);
            $table->enum('metodo_pago', ['efectivo', 'transferencia', 'deposito', 'tarjeta']);
            $table->string('referencia', 100)->nullable();
            $table->dateTime('fecha_pago');
            $table->text('notas')->nullable();
            $table->enum('estado', ['aplicado', 'anulado'])->default('aplicado');
            $table->string('motivo_anulacion', 300)->nullable();
            $table->timestamps();

            $table->index(['fecha_pago', 'estado']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
