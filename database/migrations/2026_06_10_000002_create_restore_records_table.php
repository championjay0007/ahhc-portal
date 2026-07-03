<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restore_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('backup_record_id')->nullable()->constrained('backup_records')->nullOnDelete();
            $table->timestamp('restore_date');
            $table->string('status');
            $table->foreignId('initiated_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restore_records');
    }
};
