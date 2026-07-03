<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->string('theme')->default('clean')->after('category');
            $table->text('custom_style')->nullable()->after('theme');
        });
    }

    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->dropColumn(['theme', 'custom_style']);
        });
    }
};
