<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_template_id')->constrained('email_templates')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('name');
            $table->string('slug');
            $table->string('subject');
            $table->text('html_body');
            $table->text('text_body')->nullable();
            $table->json('variables')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['email_template_id', 'version_number']);
            $table->foreign('category_id')
                ->references('id')
                ->on('email_template_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_template_versions');
    }
};
