<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participant_id');
            $table->json('personal_data');
            $table->json('support_person_data')->nullable();
            $table->json('uploaded_documents')->nullable();
            $table->json('signed_agreements')->nullable();
            $table->string('status')->default('pending_review'); // invitation_sent, in_progress, pending_review, changes_requested, approved, rejected, activated
            $table->text('admin_comments')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();

            $table->foreign('participant_id')->references('id')->on('participants')->restrictOnDelete();
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
            $table->index('participant_id');
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_submissions');
    }
};
