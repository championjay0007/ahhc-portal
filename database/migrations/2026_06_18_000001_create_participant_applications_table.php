<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_applications', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('postcode');
            $table->string('disability_category')->nullable();
            $table->text('support_needs')->nullable();
            $table->string('funding_source')->nullable();
            $table->string('status')->default('new_application'); // new_application, under_review, approved, rejected
            $table->text('rejected_reason')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamps();

            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->index('status');
            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_applications');
    }
};
