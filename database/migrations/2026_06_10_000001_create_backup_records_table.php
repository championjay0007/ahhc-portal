<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_records', function (Blueprint $table) {
            $table->id();
            $table->string('backup_type');
            $table->timestamp('backup_date');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('status');
            $table->string('storage_location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_records');
    }
};
