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
        Schema::table('draw_periods', function (Blueprint $table) {
            $table->boolean('is_public')->default(false)->after('draw_executed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('draw_periods', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
