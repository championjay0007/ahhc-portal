<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pre_approval_requests')) {
            Schema::table('pre_approval_requests', function (Blueprint $table) {
                if (! Schema::hasColumn('pre_approval_requests', 'supplier_id')) {
                    $table->foreignId('supplier_id')->nullable()->constrained('workers')->nullOnDelete()->after('worker_id');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'estimated_amount_cents')) {
                    $table->unsignedInteger('estimated_amount_cents')->nullable()->after('requested_amount_cents');
                }
                if (! Schema::hasColumn('pre_approval_requests', 'expiry_date')) {
                    $table->date('expiry_date')->nullable()->after('end_date');
                }
            });
        }

        if (! Schema::hasTable('pre_approval_attachments')) {
            Schema::create('pre_approval_attachments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pre_approval_request_id')->constrained('pre_approval_requests')->cascadeOnDelete();
                $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('title')->nullable();
                $table->string('file_path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pre_approval_comments')) {
            Schema::create('pre_approval_comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pre_approval_request_id')->constrained('pre_approval_requests')->cascadeOnDelete();
                $table->foreignId('commented_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('comment_type')->default('note');
                $table->text('message');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pre_approval_comments')) {
            Schema::dropIfExists('pre_approval_comments');
        }

        if (Schema::hasTable('pre_approval_attachments')) {
            Schema::dropIfExists('pre_approval_attachments');
        }

        if (Schema::hasTable('pre_approval_requests')) {
            Schema::table('pre_approval_requests', function (Blueprint $table) {
                if (Schema::hasColumn('pre_approval_requests', 'supplier_id')) {
                    $table->dropForeign(['supplier_id']);
                    $table->dropColumn('supplier_id');
                }
                if (Schema::hasColumn('pre_approval_requests', 'estimated_amount_cents')) {
                    $table->dropColumn('estimated_amount_cents');
                }
                if (Schema::hasColumn('pre_approval_requests', 'expiry_date')) {
                    $table->dropColumn('expiry_date');
                }
            });
        }
    }
};
