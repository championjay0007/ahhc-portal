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
                if (! Schema::hasColumn('pre_approval_requests', 'worker_id')) {
                    $table->foreignId('worker_id')->nullable()->constrained('workers')->nullOnDelete()->after('participant_id');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'service_category')) {
                    $table->string('service_category')->nullable()->after('support_person_id');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'description')) {
                    $table->text('description')->nullable()->after('service_category');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'start_date')) {
                    $table->date('start_date')->nullable()->after('description');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'end_date')) {
                    $table->date('end_date')->nullable()->after('start_date');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'quote_file_path')) {
                    $table->string('quote_file_path')->nullable()->after('end_date');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'admin_id')) {
                    $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete()->after('status');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'decision_reason')) {
                    $table->text('decision_reason')->nullable()->after('admin_id');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('decision_reason');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('pre_approval_requests')) {
            Schema::table('pre_approval_requests', function (Blueprint $table) {
                if (Schema::hasColumn('pre_approval_requests', 'worker_id')) {
                    $table->dropForeign(['worker_id']);
                    $table->dropColumn('worker_id');
                }
                if (Schema::hasColumn('pre_approval_requests', 'service_category')) {
                    $table->dropColumn('service_category');
                }
                if (Schema::hasColumn('pre_approval_requests', 'description')) {
                    $table->dropColumn('description');
                }
                if (Schema::hasColumn('pre_approval_requests', 'start_date')) {
                    $table->dropColumn('start_date');
                }
                if (Schema::hasColumn('pre_approval_requests', 'end_date')) {
                    $table->dropColumn('end_date');
                }
                if (Schema::hasColumn('pre_approval_requests', 'quote_file_path')) {
                    $table->dropColumn('quote_file_path');
                }
                if (Schema::hasColumn('pre_approval_requests', 'admin_id')) {
                    $table->dropForeign(['admin_id']);
                    $table->dropColumn('admin_id');
                }
                if (Schema::hasColumn('pre_approval_requests', 'decision_reason')) {
                    $table->dropColumn('decision_reason');
                }
                if (Schema::hasColumn('pre_approval_requests', 'approved_at')) {
                    $table->dropColumn('approved_at');
                }
            });
        }
    }
};
