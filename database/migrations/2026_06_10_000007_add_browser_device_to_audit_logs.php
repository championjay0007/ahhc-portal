<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'browser')) {
                $table->string('browser')->nullable();
            }
            if (! Schema::hasColumn('audit_logs', 'device')) {
                $table->string('device')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'browser')) {
                $table->dropColumn('browser');
            }
            if (Schema::hasColumn('audit_logs', 'device')) {
                $table->dropColumn('device');
            }
        });
    }
};
