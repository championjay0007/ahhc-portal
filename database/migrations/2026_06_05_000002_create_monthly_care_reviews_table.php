<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monthly_care_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('care_manager_id')->constrained('users')->cascadeOnDelete();
            $table->date('review_date')->nullable();
            $table->string('review_type')->default('Standard'); // Standard, Interim, Annual, etc.
            $table->text('notes')->nullable();
            $table->text('concerns')->nullable();
            $table->text('actions_required')->nullable();
            $table->date('next_review_date')->nullable();
            $table->string('status')->default('Due'); // Due, Completed, Overdue
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('due_date_reminder_sent_at')->nullable(); // 7 days before
            $table->timestamp('today_reminder_sent_at')->nullable(); // Day of
            $table->timestamp('overdue_reminder_sent_at')->nullable(); // When it becomes overdue
            $table->foreignId('completed_by_id')->nullable()->constrained('users'); // Who completed it
            $table->text('completion_notes')->nullable();
            $table->timestamps();

            // Indexes for efficient querying
            $table->index('participant_id');
            $table->index('care_manager_id');
            $table->index('status');
            $table->index('review_date');
            $table->index('next_review_date');
            $table->index('created_at');
        });

        Schema::create('care_review_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_care_review_id')->constrained('monthly_care_reviews')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('activity_type'); // created, updated, completed, viewed, etc.
            $table->string('description');
            $table->json('changes')->nullable(); // For tracking what was changed
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('monthly_care_review_id');
            $table->index('user_id');
            $table->index('activity_type');
            $table->index('created_at');
        });

        Schema::create('care_contact_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('care_manager_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('monthly_care_review_id')->nullable()->constrained('monthly_care_reviews')->cascadeOnDelete();
            $table->dateTime('contact_datetime');
            $table->string('contact_type'); // Phone, In-person, Email, Virtual, etc.
            $table->string('contact_method'); // Initiated By, etc.
            $table->text('notes')->nullable();
            $table->text('outcomes')->nullable();
            $table->boolean('follow_up_required')->default(false);
            $table->date('follow_up_date')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('participant_id');
            $table->index('care_manager_id');
            $table->index('monthly_care_review_id');
            $table->index('contact_datetime');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('care_contact_logs');
        Schema::dropIfExists('care_review_activities');
        Schema::dropIfExists('monthly_care_reviews');
    }
};
