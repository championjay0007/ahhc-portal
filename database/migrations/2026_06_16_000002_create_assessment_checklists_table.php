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
        Schema::create('assessment_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');

            // Eligibility Assessment
            $table->boolean('identity_confirmed')->default(false);
            $table->boolean('contact_details_verified')->default(false);
            $table->boolean('address_verified')->default(false);
            $table->boolean('support_at_home_eligibility_confirmed')->default(false);
            $table->boolean('program_eligibility_confirmed')->default(false);

            // Suitability Assessment
            $table->boolean('can_manage_workers')->default(false);
            $table->boolean('can_make_service_decisions')->default(false);
            $table->boolean('can_approve_invoices')->default(false);
            $table->boolean('can_review_spending')->default(false);
            $table->boolean('understands_responsibilities')->default(false);

            // Funding Assessment
            $table->boolean('funding_verified')->default(false);
            $table->boolean('funding_documentation_received')->default(false);
            $table->boolean('budget_confirmed')->default(false);

            // Profile Setup
            $table->boolean('participant_profile_completed')->default(false);
            $table->boolean('emergency_contact_added')->default(false);
            $table->boolean('support_person_added')->default(false);

            // Plans and Budget
            $table->boolean('care_plan_created')->default(false);
            $table->boolean('support_plan_created')->default(false);
            $table->boolean('budget_configured')->default(false);
            $table->boolean('quarter_configured')->default(false);
            $table->boolean('carry_over_configured')->default(false);

            // Documents
            $table->boolean('referral_documents_collected')->default(false);
            $table->boolean('participant_documents_collected')->default(false);
            $table->boolean('authority_documents_collected')->default(false);
            $table->boolean('funding_documents_collected')->default(false);

            // Agreements
            $table->boolean('agreements_assigned')->default(false);

            // Onboarding completion
            $table->boolean('mfa_enabled')->default(false);
            $table->boolean('profile_confirmed')->default(false);
            $table->boolean('documents_uploaded')->default(false);
            $table->boolean('agreements_signed')->default(false);
            $table->boolean('support_person_approved')->default(false);
            $table->boolean('all_onboarding_steps_completed')->default(false);

            // Calculate completion percentage
            $table->unsignedTinyInteger('completion_percentage')->default(0);

            $table->timestamps();

            $table->index('assessment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_checklists');
    }
};
