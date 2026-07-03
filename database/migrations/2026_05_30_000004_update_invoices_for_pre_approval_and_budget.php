<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('pre_approval_id')->nullable()->constrained('pre_approval_requests')->nullOnDelete()->after('worker_id');
            $table->unsignedInteger('amount_cents')->default(0)->after('invoice_number');
            $table->date('service_date')->nullable()->after('due_date');
            $table->string('invoice_file_path')->nullable()->after('invoice_date');
        });

        DB::table('invoices')->update(['amount_cents' => DB::raw('total_cents')]);
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['pre_approval_id']);
            $table->dropColumn(['pre_approval_id', 'amount_cents', 'service_date', 'invoice_file_path']);
        });
    }
};
