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
        Schema::create('draw_period_prize', function (Blueprint $table) {
            $table->id();
            $table->foreignId('draw_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('prize_id')->constrained()->onDelete('cascade');
            $table->integer('max_quantity')->default(0);
            $table->integer('awarded_quantity')->default(0);
            $table->timestamps();

            $table->unique(['draw_period_id', 'prize_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draw_period_prize');
    }
};
