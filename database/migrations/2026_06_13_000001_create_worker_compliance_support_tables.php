<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_compliance_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_critical')->default(false);
            $table->string('default_status')->default('Missing');
            $table->timestamps();
        });

        Schema::table('worker_compliance_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('worker_compliance_documents', 'worker_compliance_type_id')) {
                $table->foreignId('worker_compliance_type_id')
                    ->nullable()
                    ->constrained('worker_compliance_types')
                    ->nullOnDelete()
                    ->after('worker_id');
            }
        });

        Schema::create('worker_compliance_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();
            $table->foreignId('worker_compliance_document_id')->nullable()->constrained('worker_compliance_documents')->cascadeOnDelete();
            $table->string('alert_type');
            $table->string('alert_level')->nullable();
            $table->string('document_type')->nullable();
            $table->text('message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::table('workers', function (Blueprint $table) {
            if (! Schema::hasColumn('workers', 'compliance_suspended_at')) {
                $table->timestamp('compliance_suspended_at')->nullable()->after('background_check_expiry_at');
            }
            if (! Schema::hasColumn('workers', 'compliance_suspension_reason')) {
                $table->text('compliance_suspension_reason')->nullable()->after('compliance_suspended_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('worker_compliance_documents', function (Blueprint $table) {
            if (Schema::hasColumn('worker_compliance_documents', 'worker_compliance_type_id')) {
                $table->dropForeign(['worker_compliance_type_id']);
                $table->dropColumn('worker_compliance_type_id');
            }
        });

        Schema::dropIfExists('worker_compliance_alerts');
        Schema::dropIfExists('worker_compliance_types');

        Schema::table('workers', function (Blueprint $table) {
            if (Schema::hasColumn('workers', 'compliance_suspension_reason')) {
                $table->dropColumn('compliance_suspension_reason');
            }
            if (Schema::hasColumn('workers', 'compliance_suspended_at')) {
                $table->dropColumn('compliance_suspended_at');
            }
        });
    }
};
