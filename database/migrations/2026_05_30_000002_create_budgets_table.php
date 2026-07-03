<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('participant_id')->nullable()->constrained()->cascadeOnDelete();
            $table->date('quarter_start_date')->nullable();
            $table->date('quarter_end_date')->nullable();
            $table->integer('opening_balance_cents')->default(0);
            $table->integer('carry_over_cents')->default(0);
            $table->integer('committed_cents')->default(0);
            $table->integer('approved_spend_cents')->default(0);
            $table->integer('paid_spend_cents')->default(0);
            $table->timestamps();
            $table->unique(['participant_id', 'quarter_start_date', 'quarter_end_date'], 'budgets_participant_quarter_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('budgets');
    }
};
