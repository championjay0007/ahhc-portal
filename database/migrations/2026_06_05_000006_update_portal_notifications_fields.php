<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('portal_notifications', function (Blueprint $table) {
            if (! Schema::hasColumn('portal_notifications', 'recipient_id')) {
                $table->unsignedBigInteger('recipient_id')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('portal_notifications', 'title')) {
                $table->string('title')->nullable()->after('recipient_id');
            }

            if (! Schema::hasColumn('portal_notifications', 'message')) {
                $table->text('message')->nullable()->after('title');
            }

            if (! Schema::hasColumn('portal_notifications', 'channel')) {
                $table->string('channel')->nullable()->after('message');
            }

            if (! Schema::hasColumn('portal_notifications', 'is_sent')) {
                $table->boolean('is_sent')->default(false)->after('channel');
            }
        });
    }

    public function down(): void
    {
        Schema::table('portal_notifications', function (Blueprint $table) {
            if (Schema::hasColumn('portal_notifications', 'is_sent')) {
                $table->dropColumn('is_sent');
            }
            if (Schema::hasColumn('portal_notifications', 'channel')) {
                $table->dropColumn('channel');
            }
            if (Schema::hasColumn('portal_notifications', 'message')) {
                $table->dropColumn('message');
            }
            if (Schema::hasColumn('portal_notifications', 'title')) {
                $table->dropColumn('title');
            }
            if (Schema::hasColumn('portal_notifications', 'recipient_id')) {
                $table->dropColumn('recipient_id');
            }
        });
    }
};
