<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_export_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('report_type');
            $table->string('export_format');
            $table->json('filters')->nullable();
            $table->integer('record_count')->default(0);
            $table->string('file_path')->nullable();
            $table->integer('file_size')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('exported_at');
            $table->timestamps();

            $table->index('user_id');
            $table->index('report_type');
            $table->index('export_format');
            $table->index('exported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_export_logs');
    }
};
