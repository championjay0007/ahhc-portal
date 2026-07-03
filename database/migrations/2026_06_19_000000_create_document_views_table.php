<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('document_views', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('participant_id')->nullable()->index();
            $table->unsignedBigInteger('document_id')->nullable()->index();
            $table->string('supporting_id')->index();
            $table->timestamp('viewed_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_views');
    }
};
