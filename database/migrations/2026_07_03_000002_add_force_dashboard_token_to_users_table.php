<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'force_dashboard_token')) {
                $table->string('force_dashboard_token')->nullable()->after('force_dashboard');
            }
            if (! Schema::hasColumn('users', 'force_dashboard_token_expires_at')) {
                $table->timestamp('force_dashboard_token_expires_at')->nullable()->after('force_dashboard_token');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'force_dashboard_token')) {
                $table->dropColumn('force_dashboard_token');
            }
            if (Schema::hasColumn('users', 'force_dashboard_token_expires_at')) {
                $table->dropColumn('force_dashboard_token_expires_at');
            }
        });
    }
};
