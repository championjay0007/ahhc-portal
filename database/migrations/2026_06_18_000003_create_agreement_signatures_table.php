<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreement_signatures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agreement_id');
            $table->unsignedBigInteger('participant_id');
            $table->longText('signature_image');
            $table->timestamp('signed_at');
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('agreement_id')->references('id')->on('agreements')->restrictOnDelete();
            $table->foreign('participant_id')->references('id')->on('participants')->restrictOnDelete();
            $table->unique(['agreement_id', 'participant_id']);
            $table->index('signed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_signatures');
    }
};
