<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iso_code', 2);
            $table->string('phone_code', 10);
            $table->string('id_format');
            $table->string('phone_format');
            $table->integer('phone_min_length');
            $table->integer('phone_max_length');
            $table->integer('id_min_length');
            $table->integer('id_max_length');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('countries');
        Schema::enableForeignKeyConstraints();
    }
};
