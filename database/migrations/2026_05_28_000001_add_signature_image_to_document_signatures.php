<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('document_signatures')) {
            Schema::table('document_signatures', function (Blueprint $table) {
                if (! Schema::hasColumn('document_signatures', 'signature_path')) {
                    $table->string('signature_path')->nullable()->after('signature_hash');
                }

                if (! Schema::hasColumn('document_signatures', 'signature_disk')) {
                    $table->string('signature_disk')->default('local')->after('signature_path');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('document_signatures')) {
            Schema::table('document_signatures', function (Blueprint $table) {
                if (Schema::hasColumn('document_signatures', 'signature_path')) {
                    $table->dropColumn('signature_path');
                }

                if (Schema::hasColumn('document_signatures', 'signature_disk')) {
                    $table->dropColumn('signature_disk');
                }
            });
        }
    }
};
