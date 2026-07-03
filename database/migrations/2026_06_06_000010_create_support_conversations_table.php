<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('support_conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('admin_id')->nullable()->index();
            $table->string('subject');
            $table->enum('status', ['open', 'in-progress', 'waiting', 'resolved', 'closed'])->default('open')->index();
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->index();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_conversations');
    }
};
