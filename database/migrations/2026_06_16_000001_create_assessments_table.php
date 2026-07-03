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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->nullable()->constrained('participants')->onDelete('cascade');
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('restrict');

            // Status tracking
            $table->string('status')->default('new_enquiry'); // new_enquiry, under_review, awaiting_information, etc.
            $table->string('previous_status')->nullable();

            // Assessment data
            $table->string('enquiry_source')->nullable(); // website, referral, phone, etc.
            $table->text('enquiry_notes')->nullable();
            $table->string('funding_source')->nullable();
            $table->string('funding_type')->nullable();
            $table->text('funding_details')->nullable();
            $table->decimal('budget_allocation', 10, 2)->nullable();

            // Support person info
            $table->boolean('support_person_required')->default(false);
            $table->string('support_person_name')->nullable();
            $table->string('support_person_relationship')->nullable();
            $table->string('support_person_email')->nullable();
            $table->string('support_person_phone')->nullable();
            $table->string('support_person_authority_level')->nullable(); // view_only, document_access, representative_access

            // Assessment outcomes
            $table->string('eligibility_outcome')->nullable(); // eligible, not_eligible, further_information
            $table->string('suitability_outcome')->nullable(); // suitable, suitable_with_support, not_suitable
            $table->string('funding_verification_outcome')->nullable(); // verified, not_verified, further_information
            $table->string('overall_decision')->nullable(); // approved, rejected, pending, awaiting_information

            // Decision data
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Invitation control
            $table->string('invitation_token')->nullable()->unique();
            $table->dateTime('invitation_sent_at')->nullable();
            $table->dateTime('invitation_expires_at')->nullable();
            $table->dateTime('invitation_accepted_at')->nullable();

            // Onboarding tracking
            $table->dateTime('onboarding_started_at')->nullable();
            $table->dateTime('onboarding_completed_at')->nullable();
            $table->dateTime('final_review_started_at')->nullable();
            $table->dateTime('final_review_completed_at')->nullable();
            $table->dateTime('activated_at')->nullable();
            $table->foreignId('activated_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            // Metadata
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indices for performance
            $table->index('participant_id');
            $table->index('assigned_to_user_id');
            $table->index('status');
            $table->index('overall_decision');
            $table->index('invitation_token');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
