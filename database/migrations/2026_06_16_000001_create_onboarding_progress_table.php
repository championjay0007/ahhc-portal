<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('current_step')->default(1);
            $table->json('completed_steps')->nullable();
            $table->json('draft_data')->nullable();
            $table->string('status')->default('in_progress');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_progress');
    }
};
