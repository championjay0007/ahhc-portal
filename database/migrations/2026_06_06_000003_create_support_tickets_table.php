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
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->longText('description');
            $table->string('category')->default('general'); // general, billing, technical, account, other
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            $table->string('status')->default('open'); // open, in-progress, waiting, resolved, closed
            $table->string('attachment_path')->nullable();
            $table->dateTime('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'status']);
            $table->index(['priority', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
