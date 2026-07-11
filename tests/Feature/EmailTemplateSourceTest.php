<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\PortalSetting;
use App\Services\EmailTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailTemplateSourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_code_mode_uses_the_default_message_instead_of_database_template(): void
    {
        PortalSetting::query()->where('key', 'email_template_source')->delete();
        PortalSetting::create([
            'key' => 'email_template_source',
            'value' => 'code',
        ]);

        EmailTemplate::create([
            'name' => 'Example Template',
            'slug' => 'example-template',
            'subject' => 'Database subject',
            'html_body' => '<p>Hello {{name}}</p>',
            'text_body' => 'Hello {{name}}',
            'category' => 'General',
            'is_active' => true,
        ]);

        $rendered = EmailTemplateService::renderTemplate(
            'example-template',
            ['name' => 'Jane'],
            'Default subject',
            '<p>Default html for Jane</p>',
            'Default text for Jane',
            'Example Template',
            'General'
        );

        $this->assertSame('Default subject', $rendered['subject']);
        $this->assertStringContainsString('Default html for Jane', $rendered['html']);
        $this->assertSame('Default text for Jane', $rendered['text']);
    }

    public function test_built_in_templates_include_the_polished_onboarding_and_activation_content(): void
    {
        $definitions = EmailTemplateService::getBuiltInTemplateDefinitions();
        $slugs = array_column($definitions, 'slug');

        $this->assertContains('participant-onboarding-invitation', $slugs);
        $this->assertContains('account-activated', $slugs);
        $this->assertContains('onboarding-status', $slugs);

        $participantTemplate = collect($definitions)->firstWhere('slug', 'participant-onboarding-invitation');
        $this->assertStringContainsString('Continue onboarding', $participantTemplate['html']);
        $this->assertStringContainsString('Welcome,', $participantTemplate['html']);
    }

    public function test_missing_template_source_setting_defaults_to_database_templates(): void
    {
        PortalSetting::query()->where('key', 'email_template_source')->delete();

        EmailTemplate::create([
            'name' => 'Example Template',
            'slug' => 'example-template',
            'subject' => 'Database subject',
            'html_body' => '<p>Hello {{name}}</p>',
            'text_body' => 'Hello {{name}}',
            'category' => 'General',
            'is_active' => true,
        ]);

        $rendered = EmailTemplateService::renderTemplate(
            'example-template',
            ['name' => 'Jane'],
            'Default subject',
            '<p>Default html for Jane</p>',
            'Default text for Jane',
            'Example Template',
            'General'
        );

        $this->assertSame('Database subject', $rendered['subject']);
        $this->assertStringContainsString('Hello Jane', $rendered['html']);
        $this->assertSame('Hello Jane', $rendered['text']);
    }

    public function test_database_source_uses_inactive_database_template_body_strictly(): void
    {
        PortalSetting::query()->where('key', 'email_template_source')->delete();
        PortalSetting::create([
            'key' => 'email_template_source',
            'value' => 'database',
        ]);

        EmailTemplate::create([
            'name' => 'Example Template',
            'slug' => 'example-template',
            'subject' => 'Database subject (inactive)',
            'html_body' => '<p>Stored DB body for {{name}}</p>',
            'text_body' => 'Stored DB text for {{name}}',
            'category' => 'General',
            'is_active' => false,
        ]);

        $rendered = EmailTemplateService::renderTemplate(
            'example-template',
            ['name' => 'Jane'],
            'Default subject',
            '<p>Default html for Jane</p>',
            'Default text for Jane',
            'Example Template',
            'General'
        );

        $this->assertSame('Database subject (inactive)', $rendered['subject']);
        $this->assertStringContainsString('Stored DB body for Jane', $rendered['html']);
        $this->assertSame('Stored DB text for Jane', $rendered['text']);
    }
}
