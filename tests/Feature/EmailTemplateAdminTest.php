<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmailTemplateAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_template_with_custom_variables_and_generated_plain_text(): void
    {
        $admin = User::create([
            'name' => 'Email Template Admin',
            'email' => 'email-template-admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => true,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('portal.admin.messages.email_templates.store'), [
            'name' => 'Welcome Email',
            'subject' => 'Hello {{name}}',
            'html_body' => '<p>Hi {{name}}</p><p>Your code is {{activation_code}}</p>',
            'text_body' => '',
            'variables' => "name\nemail\ncustom_value",
            'category' => 'Onboarding',
            'is_active' => true,
        ]);

        $response->assertRedirect(route('portal.admin.messages.email_templates.index'));

        $template = EmailTemplate::latest()->first();

        $this->assertNotNull($template);
        $this->assertSame("Hi {{name}}\n\nYour code is {{activation_code}}", $template->text_body);
        $this->assertEqualsCanonicalizing(['name', 'activation_code', 'email', 'custom_value'], $template->variables);
    }
}
