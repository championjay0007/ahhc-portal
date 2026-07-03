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
        Schema::create('assessment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('uploaded_by_user_id')->constrained('users')->onDelete('restrict');

            $table->string('document_category'); // referral, care_plan, support_plan, authority, funding, participant_doc
            $table->string('document_type')->nullable();
            $table->string('document_name');
            $table->string('file_name');
            $table->string('storage_disk')->default('secure');
            $table->string('storage_path');
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');

            $table->string('status')->default('received'); // received, pending, missing, rejected
            $table->text('rejection_reason')->nullable();
            $table->dateTime('rejected_at')->nullable();
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();

            $table->timestamps();

            $table->index('assessment_id');
            $table->index('document_category');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_documents');
    }
};
