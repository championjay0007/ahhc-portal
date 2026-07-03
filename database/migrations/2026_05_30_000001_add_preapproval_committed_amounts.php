<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pre_approval_requests')) {
            Schema::table('pre_approval_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('pre_approval_requests', 'committed_amount_cents')) {
                    $table->unsignedInteger('committed_amount_cents')->nullable()->after('requested_amount_cents');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'review_notes')) {
                    $table->text('review_notes')->nullable()->after('notes');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pre_approval_requests')) {
            Schema::table('pre_approval_requests', function (Blueprint $table) {
                if (Schema::hasColumn('pre_approval_requests', 'committed_amount_cents')) {
                    $table->dropColumn('committed_amount_cents');
                }
                if (Schema::hasColumn('pre_approval_requests', 'review_notes')) {
                    $table->dropColumn('review_notes');
                }
            });
        }
    }
};
