<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->nullable()->constrained()->nullOnDelete();
            $table->string('service_type')->nullable();
            $table->string('service_category')->nullable();
            $table->date('shift_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::table('care_notes', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('worker_id')->constrained('shifts')->nullOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('worker_id')->constrained('shifts')->nullOnDelete();
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('worker_id')->constrained('shifts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_id');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_id');
        });

        Schema::table('care_notes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_id');
        });

        Schema::dropIfExists('shifts');
    }
};
