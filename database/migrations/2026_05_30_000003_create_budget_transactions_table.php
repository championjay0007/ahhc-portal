<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budget_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->integer('amount_cents');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_transactions');
    }
};
