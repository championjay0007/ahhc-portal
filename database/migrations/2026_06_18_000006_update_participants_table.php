<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            // Add application_id foreign key
            if (! Schema::hasColumn('participants', 'application_id')) {
                $table->unsignedBigInteger('application_id')->nullable()->after('user_id');
                $table->foreign('application_id')->references('id')->on('participant_applications')->nullOnDelete();
            }

            // Add onboarding status
            if (! Schema::hasColumn('participants', 'onboarding_status')) {
                $table->string('onboarding_status')->default('new')->after('status');
            }

            // Add onboarding token and expiry
            if (! Schema::hasColumn('participants', 'onboarding_token')) {
                $table->string('onboarding_token')->unique()->nullable()->after('onboarding_status');
                $table->timestamp('onboarding_expires_at')->nullable()->after('onboarding_token');
            }

            // Add approval timestamp
            if (! Schema::hasColumn('participants', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('onboarding_expires_at');
            }

            // Add activated timestamp
            if (! Schema::hasColumn('participants', 'activated_at')) {
                $table->timestamp('activated_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('participants', function (Blueprint $table) {
            if (Schema::hasColumn('participants', 'activated_at')) {
                $table->dropColumn('activated_at');
            }
            if (Schema::hasColumn('participants', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('participants', 'onboarding_expires_at')) {
                $table->dropColumn('onboarding_expires_at');
            }
            if (Schema::hasColumn('participants', 'onboarding_token')) {
                $table->dropColumn('onboarding_token');
            }
            if (Schema::hasColumn('participants', 'onboarding_status')) {
                $table->dropColumn('onboarding_status');
            }
            if (Schema::hasColumn('participants', 'application_id')) {
                $table->dropForeign(['application_id']);
                $table->dropColumn('application_id');
            }
        });
    }
};
