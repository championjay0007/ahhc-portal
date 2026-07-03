<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardAvailableDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_zero_budget_values_for_participant(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $participantUser = User::create([
            'name' => 'Participant User',
            'email' => 'participant@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-9001',
            'first_name' => 'NoBudget',
            'last_name' => 'Member',
            'status' => 'active',
            'phone' => '0400000000',
            'email' => 'nobudget@example.com',
            'budget_limit_cents' => 0,
            'current_budget_used_cents' => 0,
        ]);

        $response = $this->actingAs($admin)
            ->get(route('portal.admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('NoBudget');
        $response->assertSee('$0');
    }

    public function test_dashboard_shows_zero_pending_invoices_count_when_none_exist(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin2@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('portal.admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('0 pending review');
    }
}
