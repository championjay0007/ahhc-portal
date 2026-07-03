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
        Schema::create('assessment_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('created_by_user_id')->constrained('users')->onDelete('restrict');

            $table->text('note_text');
            $table->string('note_type')->default('general'); // general, eligibility, suitability, funding, decision, information_request
            $table->boolean('is_internal')->default(true); // Internal staff notes vs participant-facing
            $table->boolean('requires_action')->default(false);

            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index('assessment_id');
            $table->index('created_by_user_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_notes');
    }
};
