<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('worker_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('worker_id')->constrained('workers')->cascadeOnDelete();

            // Declaration types: privacy, incident_reporting, care_notes, no_commencement, code_of_conduct, third_party
            $table->string('declaration_type');
            $table->text('declaration_text'); // Full text of what they're agreeing to
            $table->boolean('agreed')->default(false);
            $table->timestamp('signed_at')->nullable();
            $table->string('signature_file_path')->nullable(); // Stored signature image
            $table->timestamp('declined_at')->nullable();
            $table->text('decline_reason')->nullable();

            $table->timestamps();

            $table->index('worker_id');
            $table->index('declaration_type');
            $table->unique(['worker_id', 'declaration_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('worker_declarations');
    }
};
