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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->constrained();
            $table->string('id_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('phone_number')->nullable();
            $table->boolean('marketing_opt_in')->default(false);
            $table->boolean('whatsapp_opt_in')->default(false);
            $table->boolean('phone_opt_in')->default(false);
            $table->boolean('email_opt_in')->default(false);
            $table->boolean('sms_opt_in')->default(false);
            $table->boolean('data_treatment_accepted')->default(false);
            $table->boolean('terms_accepted')->default(false);
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
