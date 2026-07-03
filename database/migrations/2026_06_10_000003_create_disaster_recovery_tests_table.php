<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('disaster_recovery_tests', function (Blueprint $table) {
            $table->id();
            $table->timestamp('test_date');
            $table->string('status');
            $table->foreignId('conducted_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('summary')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disaster_recovery_tests');
    }
};
