<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('care_notes', function (Blueprint $table) {
            $table->date('shift_date')->nullable()->after('worker_id');
            $table->time('start_time')->nullable()->after('shift_date');
            $table->time('end_time')->nullable()->after('start_time');
            $table->text('tasks_completed')->nullable()->after('end_time');
            $table->boolean('risks_flag')->default(false)->after('observations');
            $table->string('attachment_path')->nullable()->after('risks_flag');
            $table->boolean('service_confirmed')->default(false)->after('attachment_path');
        });

        DB::table('care_notes')->whereNotNull('visit_date')->update([
            'shift_date' => DB::raw('visit_date'),
        ]);

        Schema::table('care_notes', function (Blueprint $table) {
            if (Schema::hasColumn('care_notes', 'visit_date')) {
                $table->dropColumn('visit_date');
            }
        });
    }

    public function down()
    {
        Schema::table('care_notes', function (Blueprint $table) {
            $table->date('visit_date')->nullable()->after('worker_id');
        });

        DB::table('care_notes')->whereNotNull('shift_date')->update([
            'visit_date' => DB::raw('shift_date'),
        ]);

        Schema::table('care_notes', function (Blueprint $table) {
            $table->dropColumn(['shift_date', 'start_time', 'end_time', 'tasks_completed', 'risks_flag', 'attachment_path', 'service_confirmed']);
        });
    }
};
