<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sender_id')->index();
            $table->unsignedBigInteger('recipient_id')->index();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->string('subject');
            $table->longText('body');
            $table->string('type')->default('general');
            $table->timestamp('read_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('template_id')->references('id')->on('message_templates')->onDelete('set null');

            $table->index(['recipient_id', 'read_at']);
            $table->index(['sender_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
