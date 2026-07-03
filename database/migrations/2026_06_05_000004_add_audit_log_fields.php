<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'model_type')) {
                $table->string('model_type')->nullable()->after('subject_type');
            }

            if (! Schema::hasColumn('audit_logs', 'model_id')) {
                $table->unsignedBigInteger('model_id')->nullable()->after('model_type');
            }

            if (! Schema::hasColumn('audit_logs', 'old_values')) {
                $table->json('old_values')->nullable()->after('model_id');
            }

            if (! Schema::hasColumn('audit_logs', 'new_values')) {
                $table->json('new_values')->nullable()->after('old_values');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'new_values')) {
                $table->dropColumn('new_values');
            }
            if (Schema::hasColumn('audit_logs', 'old_values')) {
                $table->dropColumn('old_values');
            }
            if (Schema::hasColumn('audit_logs', 'model_id')) {
                $table->dropColumn('model_id');
            }
            if (Schema::hasColumn('audit_logs', 'model_type')) {
                $table->dropColumn('model_type');
            }
        });
    }
};
