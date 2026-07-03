<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            if (! Schema::hasColumn('document_signatures', 'signature_request_id')) {
                $table->foreignId('signature_request_id')->nullable()->constrained('signature_requests')->nullOnDelete();
            }
            if (! Schema::hasColumn('document_signatures', 'signed_document_path')) {
                $table->string('signed_document_path')->nullable();
            }
            if (! Schema::hasColumn('document_signatures', 'signed_document_disk')) {
                $table->string('signed_document_disk')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            if (Schema::hasColumn('document_signatures', 'signature_request_id')) {
                $table->dropForeign(['signature_request_id']);
                $table->dropColumn('signature_request_id');
            }
            if (Schema::hasColumn('document_signatures', 'signed_document_path')) {
                $table->dropColumn('signed_document_path');
            }
            if (Schema::hasColumn('document_signatures', 'signed_document_disk')) {
                $table->dropColumn('signed_document_disk');
            }
        });
    }
};
