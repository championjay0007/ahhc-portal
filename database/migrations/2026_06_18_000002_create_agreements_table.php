<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->string('title')->unique();
            $table->text('description')->nullable();
            $table->longText('content');
            $table->integer('version')->default(1);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->index('is_active');
            $table->index('is_required');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreements');
    }
};
