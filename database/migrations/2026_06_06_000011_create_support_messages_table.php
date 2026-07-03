<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('support_conversation_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->longText('message');
            $table->boolean('is_admin')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('support_conversation_id')->references('id')->on('support_conversations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};
