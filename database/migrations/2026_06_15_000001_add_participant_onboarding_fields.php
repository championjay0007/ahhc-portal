<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (! Schema::hasColumn('participants', 'onboarding_token')) {
                $table->string('onboarding_token')->nullable()->unique()->after('status');
            }

            if (! Schema::hasColumn('participants', 'onboarding_expires_at')) {
                $table->timestamp('onboarding_expires_at')->nullable()->after('onboarding_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (Schema::hasColumn('participants', 'onboarding_expires_at')) {
                $table->dropColumn('onboarding_expires_at');
            }
            if (Schema::hasColumn('participants', 'onboarding_token')) {
                $table->dropUnique(['onboarding_token']);
                $table->dropColumn('onboarding_token');
            }
        });
    }
};
