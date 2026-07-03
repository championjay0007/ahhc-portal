<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('budgets')) {
            Schema::create('budgets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('participant_id')->nullable()->index();
                $table->date('quarter_start');
                $table->date('quarter_end');
                $table->decimal('opening_budget', 14, 2)->default(0);
                $table->decimal('carry_over', 14, 2)->default(0);
                $table->decimal('total_available', 14, 2)->default(0);
                $table->decimal('committed_funds', 14, 2)->default(0);
                $table->decimal('pending_invoices', 14, 2)->default(0);
                $table->decimal('approved_spend', 14, 2)->default(0);
                $table->decimal('paid_spend', 14, 2)->default(0);
                $table->decimal('remaining_balance', 14, 2)->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('budgets');
    }
};
