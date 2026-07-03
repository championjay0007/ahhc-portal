<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('invoices') && ! Schema::hasColumn('invoices', 'attachment_path')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('attachment_path')->nullable()->after('notes');
                $table->string('attachment_disk')->nullable()->default('local')->after('attachment_path');
                $table->string('attachment_mime_type')->nullable()->after('attachment_disk');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn(['attachment_path', 'attachment_disk', 'attachment_mime_type']);
            });
        }
    }
};
