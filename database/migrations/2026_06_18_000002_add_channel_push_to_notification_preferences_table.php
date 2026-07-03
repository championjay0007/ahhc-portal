<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            if (! Schema::hasColumn('notification_preferences', 'channel_push')) {
                $table->boolean('channel_push')->default(true)->after('channel_in_app');
            }
        });

        DB::table('notification_preferences')
            ->whereNull('channel_push')
            ->update(['channel_push' => true]);
    }

    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            if (Schema::hasColumn('notification_preferences', 'channel_push')) {
                $table->dropColumn('channel_push');
            }
        });
    }
};
