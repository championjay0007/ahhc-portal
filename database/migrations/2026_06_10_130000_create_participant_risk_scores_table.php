<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('participant_risk_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->integer('score');
            $table->string('level');
            $table->json('trigger_reasons')->nullable();
            $table->json('score_breakdown')->nullable();
            $table->unsignedBigInteger('calculated_by_id')->nullable();
            $table->timestamp('calculated_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('participant_risk_scores');
    }
};
