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
        Schema::create('draw_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Ej: "Semana 1", "01/01 - 07/01"
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->integer('weekly_winners_target'); // Meta de ganadores para esta semana
            $table->boolean('draw_executed')->default(false); // Si ya se ejecutó el sorteo
            $table->timestamp('draw_executed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draw_periods');
    }
};
