<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'two_factor_secret')) {
                $table->text('two_factor_secret')->after('password')->nullable();
            }

            if (! Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->text('two_factor_recovery_codes')->after('two_factor_secret')->nullable();
            }

            if (! Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->timestamp('two_factor_confirmed_at')->after('two_factor_recovery_codes')->nullable();
            }

            if (! Schema::hasColumn('users', 'mfa_enrolled_at')) {
                $table->timestamp('mfa_enrolled_at')->after('mfa_enabled')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'two_factor_recovery_codes')) {
                $table->dropColumn('two_factor_recovery_codes');
            }

            if (Schema::hasColumn('users', 'two_factor_secret')) {
                $table->dropColumn('two_factor_secret');
            }

            if (Schema::hasColumn('users', 'two_factor_confirmed_at')) {
                $table->dropColumn('two_factor_confirmed_at');
            }

            if (Schema::hasColumn('users', 'mfa_enrolled_at')) {
                $table->dropColumn('mfa_enrolled_at');
            }
        });
    }
};
