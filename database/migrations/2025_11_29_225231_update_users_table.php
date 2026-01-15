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
            if (!Schema::hasColumn('users', 'country_id')) {
                $table->foreignId('country_id')->nullable()->constrained();
            }
            if (!Schema::hasColumn('users', 'id_type')) {
                $table->string('id_type')->nullable();
            }
            if (!Schema::hasColumn('users', 'id_number')) {
                $table->string('id_number')->nullable();
            }
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable();
            }
            if (!Schema::hasColumn('users', 'marketing_opt_in')) {
                $table->boolean('marketing_opt_in')->default(false);
            }
            if (!Schema::hasColumn('users', 'whatsapp_opt_in')) {
                $table->boolean('whatsapp_opt_in')->default(false);
            }
            if (!Schema::hasColumn('users', 'phone_opt_in')) {
                $table->boolean('phone_opt_in')->default(false);
            }
            if (!Schema::hasColumn('users', 'email_opt_in')) {
                $table->boolean('email_opt_in')->default(false);
            }
            if (!Schema::hasColumn('users', 'sms_opt_in')) {
                $table->boolean('sms_opt_in')->default(false);
            }
            if (!Schema::hasColumn('users', 'data_treatment_accepted')) {
                $table->boolean('data_treatment_accepted')->default(false);
            }
            if (!Schema::hasColumn('users', 'terms_accepted')) {
                $table->boolean('terms_accepted')->default(false);
            }
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn([
                'country_id',
                'id_type',
                'id_number',
                'phone_number',
                'marketing_opt_in',
                'whatsapp_opt_in',
                'phone_opt_in',
                'email_opt_in',
                'sms_opt_in',
                'data_treatment_accepted',
                'terms_accepted',
            ]);
        });
    }
};
