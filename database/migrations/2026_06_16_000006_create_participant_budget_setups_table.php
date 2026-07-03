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
        Schema::create('participant_budget_setups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('participant_id')->nullable()->constrained('participants')->onDelete('cascade');
            $table->foreignId('configured_by_user_id')->constrained('users')->onDelete('restrict');

            // Quarter information
            $table->unsignedSmallInteger('financial_year'); // 2026, 2027, etc.
            $table->unsignedTinyInteger('quarter'); // 1-4
            $table->date('quarter_start_date');
            $table->date('quarter_end_date');

            // Budget configuration
            $table->decimal('opening_budget', 12, 2);
            $table->decimal('carry_over_amount', 12, 2)->default(0);
            $table->decimal('total_available_budget', 12, 2); // opening + carry_over

            // Budget usage tracking
            $table->decimal('approved_invoices_total', 12, 2)->default(0);
            $table->decimal('pending_invoices_total', 12, 2)->default(0);
            $table->decimal('remaining_budget', 12, 2);

            // Budget categories
            $table->json('budget_categories')->nullable(); // Array of category allocations

            // Status
            $table->string('status')->default('active'); // active, inactive, superseded
            $table->boolean('is_current')->default(true);

            // Notes
            $table->text('setup_notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('assessment_id');
            $table->index('participant_id');
            $table->index('financial_year');
            $table->index('quarter');
            $table->index('is_current');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participant_budget_setups');
    }
};
