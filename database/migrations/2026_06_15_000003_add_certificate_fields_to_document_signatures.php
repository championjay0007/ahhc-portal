<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            if (! Schema::hasColumn('document_signatures', 'certificate_path')) {
                $table->string('certificate_path')->nullable();
            }
            if (! Schema::hasColumn('document_signatures', 'certificate_disk')) {
                $table->string('certificate_disk')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('document_signatures', function (Blueprint $table) {
            if (Schema::hasColumn('document_signatures', 'certificate_path')) {
                $table->dropColumn('certificate_path');
            }
            if (Schema::hasColumn('document_signatures', 'certificate_disk')) {
                $table->dropColumn('certificate_disk');
            }
        });
    }
};
