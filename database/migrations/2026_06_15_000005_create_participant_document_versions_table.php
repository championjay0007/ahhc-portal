<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_document_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_document_id')->constrained()->cascadeOnDelete();
            $table->string('storage_disk')->default('local');
            $table->string('path');
            $table->string('mime_type');
            $table->unsignedBigInteger('size_bytes');
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('version_number');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_document_versions');
    }
};
