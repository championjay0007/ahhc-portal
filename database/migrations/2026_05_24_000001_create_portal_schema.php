<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('email');
            }

            if (! Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('participant')->after('phone');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('role');
            }

            if (! Schema::hasColumn('users', 'mfa_enabled')) {
                $table->boolean('mfa_enabled')->default(false)->after('status');
            }

            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->nullable()->after('mfa_enabled');
            }

            if (! Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('timezone');
            }

            if (! Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('last_login_at');
            }
        });

        if (! Schema::hasTable('support_people')) {
            Schema::create('support_people', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('relationship')->nullable();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('postcode')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('participants')) {
            Schema::create('participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
                $table->string('participant_number')->unique();
                $table->string('first_name');
                $table->string('last_name');
                $table->date('date_of_birth')->nullable();
                $table->string('preferred_name')->nullable();
                $table->string('status')->default('active');
                $table->date('care_plan_start_date')->nullable();
                $table->date('care_plan_end_date')->nullable();
                $table->string('primary_language')->nullable();
                $table->string('address')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('postcode')->nullable();
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('medical_alerts')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('consent_to_share')->default(false);
                $table->unsignedInteger('budget_limit_cents')->default(0);
                $table->unsignedInteger('current_budget_used_cents')->default(0);
                $table->foreignId('assigned_support_person_id')->nullable()->constrained('support_people');
                $table->foreignId('created_by_id')->nullable()->constrained('users');
                $table->foreignId('updated_by_id')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('workers')) {
            Schema::create('workers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->unique()->constrained()->cascadeOnDelete();
                $table->string('worker_number')->unique();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->string('role_type')->default('worker');
                $table->string('status')->default('active');
                $table->string('qualification')->nullable();
                $table->text('availability')->nullable();
                $table->date('compliance_expiry_at')->nullable();
                $table->date('background_check_expiry_at')->nullable();
                $table->string('vehicle_type')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('participant_assignments')) {
            Schema::create('participant_assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
                $table->foreignId('support_person_id')->nullable()->constrained('support_people');
                $table->date('start_date');
                $table->date('end_date')->nullable();
                $table->string('assignment_type')->default('primary');
                $table->string('status')->default('active');
                $table->boolean('is_primary')->default(true);
                $table->timestamps();
                $table->unique(['participant_id', 'worker_id', 'start_date'], 'assignments_scope_unique');
            });
        }

        if (! Schema::hasTable('care_notes')) {
            Schema::create('care_notes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
                $table->date('visit_date');
                $table->text('care_summary');
                $table->string('service_type')->nullable();
                $table->text('observations')->nullable();
                $table->string('mood')->nullable();
                $table->text('medication_administered')->nullable();
                $table->boolean('incident_reported')->default(false);
                $table->string('status')->default('draft');
                $table->timestamp('submitted_at')->nullable();
                $table->foreignId('created_by_id')->nullable()->constrained('users');
                $table->foreignId('approved_by_id')->nullable()->constrained('users');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('incidents')) {
            Schema::create('incidents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('worker_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('reported_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('incident_type');
                $table->string('severity');
                $table->text('description');
                $table->string('location')->nullable();
                $table->timestamp('occurred_at');
                $table->text('action_taken')->nullable();
                $table->string('status')->default('open');
                $table->boolean('follow_up_required')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pre_approval_requests')) {
            Schema::create('pre_approval_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('support_person_id')->nullable()->constrained('support_people')->nullOnDelete();
                $table->string('request_number')->unique();
                $table->string('service_type');
                $table->text('purpose');
                $table->unsignedInteger('requested_amount_cents');
                $table->string('status')->default('submitted');
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('invoices')) {
            Schema::create('invoices', function (Blueprint $table) {
                $table->id();
                $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
                $table->foreignId('worker_id')->nullable()->constrained()->nullOnDelete();
                $table->string('invoice_number')->unique();
                $table->string('status')->default('draft');
                $table->unsignedInteger('subtotal_cents')->default(0);
                $table->unsignedInteger('gst_cents')->default(0);
                $table->unsignedInteger('total_cents')->default(0);
                $table->date('invoice_date');
                $table->date('due_date')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('approved_at')->nullable();
                $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('documents')) {
            Schema::create('documents', function (Blueprint $table) {
                $table->id();
                $table->morphs('owner');
                $table->string('document_type');
                $table->string('title');
                $table->string('storage_disk')->default('local');
                $table->string('path');
                $table->string('mime_type');
                $table->unsignedBigInteger('size_bytes');
                $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('status')->default('uploaded');
                $table->date('expires_at')->nullable();
                $table->boolean('is_sensitive')->default(true);
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('document_signatures')) {
            Schema::create('document_signatures', function (Blueprint $table) {
                $table->id();
                $table->foreignId('document_id')->constrained()->cascadeOnDelete();
                $table->morphs('signed_by');
                $table->string('signature_method');
                $table->timestamp('signed_at');
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->string('signature_hash');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('complaints')) {
            Schema::create('complaints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('participant_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('support_person_id')->nullable()->constrained('support_people')->nullOnDelete();
                $table->foreignId('submitted_by_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('category');
                $table->string('priority')->default('medium');
                $table->text('description');
                $table->string('status')->default('open');
                $table->timestamp('received_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('action');
                $table->morphs('subject');
                $table->json('changes')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('complaints');
        Schema::dropIfExists('document_signatures');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('pre_approval_requests');
        Schema::dropIfExists('incidents');
        Schema::dropIfExists('care_notes');
        Schema::dropIfExists('participant_assignments');
        Schema::dropIfExists('workers');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('support_people');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'role',
                'status',
                'mfa_enabled',
                'timezone',
                'last_login_at',
                'password_changed_at',
            ]);
        });
    }
};
