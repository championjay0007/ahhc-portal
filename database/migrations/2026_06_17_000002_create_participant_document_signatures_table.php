<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_document_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participant_id');
            $table->unsignedBigInteger('document_id');
            $table->timestamp('signed_at')->nullable();
            $table->text('signature_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('participant_id')->references('id')->on('participants')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_document_signatures');
    }
};
