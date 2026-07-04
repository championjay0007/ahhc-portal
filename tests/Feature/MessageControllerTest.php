<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MessageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_send_messages_page_renders_without_blade_template_errors(): void
    {
        $admin = User::create([
            'name' => 'Admin Message Sender',
            'email' => 'admin-message-sender@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('portal.admin.messages.send.index'));

        $response->assertStatus(200);
    }
}
