<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_compliance_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->string('document_type'); // Police Check, NDIS Worker Screening, etc.
            $table->string('document_path')->nullable(); // Path to stored file
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('status')->default('missing'); // Active, Expiring Soon, Expired, Missing, Rejected
            $table->text('notes')->nullable();
            $table->timestamp('last_notified_at')->nullable(); // Track last notification sent
            $table->foreignId('verified_by_id')->nullable()->constrained('users'); // Admin who verified
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Indexes for efficient querying
            $table->index('worker_id');
            $table->index('document_type');
            $table->index('status');
            $table->index('expiry_date');
            $table->unique(['worker_id', 'document_type']); // One document type per worker
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_compliance_documents');
    }
};
