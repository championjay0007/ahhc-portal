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
        Schema::create('worker_service_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();

            // Service category/type: e.g. "Personal Care", "Cleaning", "Medication Support", etc.
            $table->string('service_category');
            $table->text('description')->nullable();

            // Approval tracking
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('status')->default('pending'); // pending, approved, rejected

            // Optional: time-limited approvals
            $table->date('approval_start_date')->nullable();
            $table->date('approval_end_date')->nullable();

            $table->timestamps();

            $table->index('worker_id');
            $table->index('status');
            $table->unique(['worker_id', 'service_category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_service_approvals');
    }
};
