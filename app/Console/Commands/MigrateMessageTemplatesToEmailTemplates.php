<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Models\MessageTemplate;
use App\Models\EmailTemplate;

class MigrateMessageTemplatesToEmailTemplates extends Command
{
    protected $signature = 'migrate:message-templates-to-email';

    protected $description = 'Copy MessageTemplate rows into EmailTemplate rows so the system uses EmailTemplate only.';

    public function handle()
    {
        $this->info('Starting migration of message templates to email templates...');

        $count = 0;

        DB::beginTransaction();

        try {
            $messages = MessageTemplate::all();

            foreach ($messages as $msg) {
                // Skip if an email template with same name exists
                if (EmailTemplate::where('name', $msg->name)->exists()) {
                    $this->line("Skipping '{$msg->name}' (already exists in email_templates)");
                    continue;
                }

                $html = $msg->theme_html ?: nl2br(e($msg->body));

                $email = EmailTemplate::create([
                    'name' => $msg->name,
                    'slug' => null,
                    'subject' => $msg->subject ?? $msg->name,
                    'html_body' => $html,
                    'text_body' => $msg->body,
                    'category' => $msg->category ?? null,
                    'category_id' => null,
                    'is_active' => $msg->is_active ?? true,
                ]);

                // ensure variables are extracted and version snapshot created via model events
                $this->line("Created email template: {$email->name}");
                $count++;
            }

            DB::commit();

            $this->info("Migration complete. Created {$count} email templates.");
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Migration failed: '.$e->getMessage());
            return 1;
        }

        return 0;
    }
}
