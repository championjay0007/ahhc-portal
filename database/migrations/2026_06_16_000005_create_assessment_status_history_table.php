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
        Schema::create('assessment_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('changed_by_user_id')->constrained('users')->onDelete('restrict');

            // Status transition
            $table->string('from_status');
            $table->string('to_status');
            $table->string('transition_reason')->nullable();

            // Data captured at transition
            $table->text('old_values')->nullable(); // JSON of previous values
            $table->text('new_values')->nullable(); // JSON of new values

            // Audit trail
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index('assessment_id');
            $table->index('changed_by_user_id');
            $table->index('to_status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_status_history');
    }
};
