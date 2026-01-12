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
        Schema::table('winners', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date', 'winner_number']);
            $table->foreignId('prize_id')->nullable()->after('code_id')->constrained()->onDelete('cascade');
            $table->foreignId('draw_period_id')->nullable()->after('prize_id')->constrained()->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winners', function (Blueprint $table) {
            $table->dropForeign(['prize_id']);
            $table->dropForeign(['draw_period_id']);
            $table->dropColumn(['prize_id', 'draw_period_id']);
            $table->date('start_date')->after('country_id');
            $table->date('end_date')->after('start_date');
            $table->integer('winner_number')->after('end_date');
        });
    }
};
