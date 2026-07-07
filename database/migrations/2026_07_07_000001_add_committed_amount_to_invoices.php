<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('invoices') && ! Schema::hasColumn('invoices', 'committed_amount_cents')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->unsignedInteger('committed_amount_cents')->nullable()->after('amount_cents');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('invoices') && Schema::hasColumn('invoices', 'committed_amount_cents')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('committed_amount_cents');
            });
        }
    }
};
