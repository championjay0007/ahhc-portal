<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->string('submitted_name')->nullable()->after('subject');
            $table->string('submitted_email')->nullable()->after('submitted_name');
        });

        // Backfill submitted_name and submitted_email from subject or first message
        $conversations = DB::table('support_conversations')->get();
        foreach ($conversations as $c) {
            $submittedName = null;
            $submittedEmail = null;

            // Try to extract email from subject: "... <email>"
            if (preg_match('/<([^>]+@[^>]+)>$/', $c->subject ?? '', $m)) {
                $submittedEmail = $m[1];
            }

            // Try to extract name from subject like 'Website support request from Name'
            if (preg_match('/from\s+([^<]+)/i', $c->subject ?? '', $m2)) {
                $submittedName = trim($m2[1]);
                // Remove trailing email if present
                $submittedName = preg_replace('/<[^>]+>$/', '', $submittedName);
                $submittedName = trim($submittedName);
            }

            // If still missing, check first message body for "Visitor name:" or "Contact email:"
            if (is_null($submittedName) || is_null($submittedEmail)) {
                $firstMsg = DB::table('support_messages')
                    ->where('support_conversation_id', $c->id)
                    ->orderBy('created_at')
                    ->first();

                if ($firstMsg) {
                    if (is_null($submittedName) && preg_match('/Visitor name:\s*(.+)$/im', $firstMsg->message, $mm)) {
                        $submittedName = trim($mm[1]);
                    }
                    if (is_null($submittedEmail) && preg_match('/Contact email:\s*([^\s]+)/i', $firstMsg->message, $mm2)) {
                        $submittedEmail = trim($mm2[1]);
                    }
                }
            }

            if ($submittedName || $submittedEmail) {
                DB::table('support_conversations')->where('id', $c->id)->update([
                    'submitted_name' => $submittedName,
                    'submitted_email' => $submittedEmail,
                ]);
            }
        }
    }

    public function down()
    {
        Schema::table('support_conversations', function (Blueprint $table) {
            $table->dropColumn(['submitted_name', 'submitted_email']);
        });
    }
};
