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
        Schema::create('promocion_plan', function (Blueprint $table) {
            $table->foreignId('promocion_id')->constrained('promociones')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('planes')->cascadeOnDelete();
            $table->primary(['promocion_id', 'plan_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promocion_plan');
    }
};
