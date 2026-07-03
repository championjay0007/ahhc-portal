<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('budget_transactions')) {
            Schema::create('budget_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('budget_id')->index();
                $table->string('type');
                $table->unsignedBigInteger('category_id')->nullable()->index();
                $table->decimal('amount', 14, 2);
                $table->json('meta')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('budget_id')->references('id')->on('budgets')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('budget_categories')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('budget_transactions');
    }
};
