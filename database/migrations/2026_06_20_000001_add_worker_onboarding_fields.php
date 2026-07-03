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
        Schema::table('workers', function (Blueprint $table) {
            // Onboarding Stage: 1=Invite, 2=Compliance, 3=Review, 4=Declarations, 5=Services, 6=Assigned
            $table->tinyInteger('onboarding_stage')->default(1)->after('status');

            // Onboarding invitation tracking
            $table->string('onboarding_token')->unique()->nullable()->after('onboarding_stage');
            $table->timestamp('onboarding_expires_at')->nullable()->after('onboarding_token');
            $table->foreignId('invited_by_id')->nullable()->constrained('users')->onDelete('set null')->after('onboarding_expires_at');
            $table->timestamp('invited_at')->nullable()->after('invited_by_id');

            // Stage completion tracking
            $table->timestamp('stage_1_completed_at')->nullable()->after('invited_at');
            $table->timestamp('stage_2_submitted_at')->nullable()->after('stage_1_completed_at');
            $table->timestamp('stage_2_completed_at')->nullable()->after('stage_2_submitted_at');
            $table->foreignId('stage_2_reviewer_id')->nullable()->constrained('users')->onDelete('set null')->after('stage_2_completed_at');
            $table->timestamp('stage_3_submitted_at')->nullable()->after('stage_2_reviewer_id');
            $table->timestamp('stage_3_completed_at')->nullable()->after('stage_3_submitted_at');
            $table->foreignId('stage_3_reviewer_id')->nullable()->constrained('users')->onDelete('set null')->after('stage_3_completed_at');
            $table->timestamp('stage_4_submitted_at')->nullable()->after('stage_3_reviewer_id');
            $table->timestamp('stage_4_completed_at')->nullable()->after('stage_4_submitted_at');
            $table->timestamp('stage_5_submitted_at')->nullable()->after('stage_4_completed_at');
            $table->timestamp('stage_5_completed_at')->nullable()->after('stage_5_submitted_at');
            $table->foreignId('stage_5_approver_id')->nullable()->constrained('users')->onDelete('set null')->after('stage_5_completed_at');
            $table->timestamp('stage_6_assigned_at')->nullable()->after('stage_5_approver_id');
            $table->foreignId('stage_6_assignor_id')->nullable()->constrained('users')->onDelete('set null')->after('stage_6_assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('workers', function (Blueprint $table) {
            $table->dropColumn([
                'onboarding_stage',
                'onboarding_token',
                'onboarding_expires_at',
                'invited_by_id',
                'invited_at',
                'stage_1_completed_at',
                'stage_2_submitted_at',
                'stage_2_completed_at',
                'stage_2_reviewer_id',
                'stage_3_submitted_at',
                'stage_3_completed_at',
                'stage_3_reviewer_id',
                'stage_4_submitted_at',
                'stage_4_completed_at',
                'stage_5_submitted_at',
                'stage_5_completed_at',
                'stage_5_approver_id',
                'stage_6_assigned_at',
                'stage_6_assignor_id',
            ]);
            $table->dropForeignKeyIfExists(['invited_by_id', 'stage_2_reviewer_id', 'stage_3_reviewer_id', 'stage_5_approver_id', 'stage_6_assignor_id']);
        });
    }
};
