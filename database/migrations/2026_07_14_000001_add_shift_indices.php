<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            // Add indices for frequently queried columns
            $table->index('participant_id');
            $table->index('worker_id');
            $table->index('shift_date');
            $table->index('status');
            $table->index('reminder_sent_at');
            
            // Composite index for worker shift queries
            $table->index(['worker_id', 'shift_date']);
            
            // Composite index for admin filtering
            $table->index(['status', 'shift_date']);
        });
    }

    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropIndex(['participant_id']);
            $table->dropIndex(['worker_id']);
            $table->dropIndex(['shift_date']);
            $table->dropIndex(['status']);
            $table->dropIndex(['reminder_sent_at']);
            $table->dropIndex(['worker_id', 'shift_date']);
            $table->dropIndex(['status', 'shift_date']);
        });
    }
};
