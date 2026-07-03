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
        Schema::create('worker_nominations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->onDelete('cascade');

            // Worker Details
            $table->string('worker_full_name');
            $table->string('worker_email');
            $table->string('worker_phone');
            $table->string('worker_address')->nullable();
            $table->enum('worker_type', ['Independent', 'Mable', 'Supplier', 'Therapist', 'Other']);

            // Service Details
            $table->string('service_type');
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->text('notes')->nullable();

            // Status Tracking
            $table->enum('status', [
                'Submitted',
                'Under Review',
                'Approved',
                'Rejected',
                'Worker Invited',
                'Compliance Pending',
                'Pending Signature',
                'Active',
                'Assigned',
            ])->default('Submitted');

            // Admin Notes
            $table->text('ahhc_admin_notes')->nullable();
            $table->text('rejection_reason')->nullable();

            // Document Storage
            $table->json('uploaded_documents')->nullable();

            // Audit Trail
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('invited_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('invited_at')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('participant_id');
            $table->index('status');
            $table->index('worker_email');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_nominations');
    }
};
