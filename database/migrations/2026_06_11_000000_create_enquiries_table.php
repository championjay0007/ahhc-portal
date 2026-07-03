<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('role');
            $table->string('support_at_home_status')->nullable();
            $table->text('message');
            $table->boolean('consent')->default(false);
            $table->string('status')->default('New');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('assigned_to')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('enquiries');
    }
};
