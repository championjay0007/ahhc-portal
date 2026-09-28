<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_account_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('invited_email');
            $table->string('token_hash', 64)->nullable()->unique();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->dateTime('expires_at');
            $table->timestamps();

            $table->index(['participant_id', 'revoked_at', 'expires_at'], 'delegations_participant_status_expiry_idx');
            $table->index(['manager_user_id', 'accepted_at', 'revoked_at'], 'delegations_manager_accepted_revoked_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_account_delegations');
    }
};