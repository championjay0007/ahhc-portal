<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Invoice;
use App\Models\Participant;
use App\Models\PortalSetting;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_portal_registration_page_redirects_to_public_home(): void
    {
        $response = $this->get(route('portal.register'));
        $response->assertRedirect(route('public.home'));
    }

    public function test_portal_registration_submit_redirects_to_public_home(): void
    {
        $response = $this->post(route('portal.register.submit'), [
            'name' => 'Public Worker',
            'email' => 'public-worker@example.com',
            'phone' => '0400000000',
            'role' => 'worker',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('public.home'));
        $this->assertDatabaseMissing('users', ['email' => 'public-worker@example.com']);
    }

    public function test_portal_registration_submit_does_not_process_invalid_payload(): void
    {
        $response = $this->post(route('portal.register.submit'), [
            'name' => '',
            'email' => '',
            'phone' => '',
            'role' => '',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect(route('public.home'));
    }

    public function test_login_shows_inline_validation_feedback_on_invalid_submission(): void
    {
        $response = $this->from(route('portal.login'))
            ->followingRedirects()
            ->post(route('portal.login.submit'), [
                'email' => '',
                'password' => '',
            ]);

        $response->assertStatus(200);
        $response->assertSee('The email field is required.');
        $response->assertSee('The password field is required.');
    }

    public function test_public_registration_submit_does_not_create_profiles(): void
    {
        $response = $this->withSession(['_token' => 'testing'])->post(route('portal.register.submit'), [
            '_token' => 'testing',
            'name' => 'Worker Profile',
            'email' => 'worker-profile@example.com',
            'phone' => '0400000000',
            'role' => 'worker',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('public.home'));
        $this->assertDatabaseMissing('users', ['email' => 'worker-profile@example.com']);
        $this->assertDatabaseMissing('workers', ['email' => 'worker-profile@example.com']);
        $this->assertDatabaseMissing('participants', ['email' => 'worker-profile@example.com']);
    }

    public function test_dashboard_displays_worker_specific_content(): void
    {
        $worker = User::create([
            'name' => 'Worker Dashboard',
            'email' => 'worker-dashboard@example.com',
            'role' => 'worker',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        Worker::create([
            'user_id' => $worker->id,
            'worker_number' => 'W-'.$worker->id,
            'first_name' => 'Worker',
            'last_name' => 'Dashboard',
            'phone' => $worker->phone,
            'email' => $worker->email,
            'role_type' => 'worker',
            'status' => 'active',
            'onboarding_stage' => 6,
        ]);

        $response = $this->actingAs($worker)->get(route('portal.worker.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Good morning, Worker Dashboard');
        $response->assertSee('Assigned Participant Chat');
        $response->assertSee('Assigned shifts today');
    }

    public function test_dashboard_displays_admin_specific_content(): void
    {
        $admin = User::create([
            'name' => 'Admin Dashboard',
            'email' => 'admin-dashboard@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('portal.admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Self-Management Admin Dashboard');
        $response->assertSeeText('Admin Control Centre');
    }

    public function test_participant_dashboard_uses_budget_service_metrics_for_budget_card(): void
    {
        $participantUser = User::create([
            'name' => 'Participant Dashboard Budget',
            'email' => 'participant-dashboard-budget@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-'.$participantUser->id,
            'first_name' => 'Participant',
            'last_name' => 'Dashboard',
            'status' => 'active',
            'phone' => '0400000000',
            'email' => $participantUser->email,
            'consent_to_share' => false,
            'budget_limit_cents' => 100000,
            'current_budget_used_cents' => 0,
            'created_by_id' => $participantUser->id,
            'updated_by_id' => $participantUser->id,
        ]);

        $period = now()->startOfQuarter();

        Budget::create([
            'participant_id' => $participant->id,
            'opening_balance_cents' => 35000,
            'carry_over_cents' => 0,
            'quarter_start_date' => $period->toDateString(),
            'quarter_end_date' => $period->copy()->endOfQuarter()->toDateString(),
        ]);

        Invoice::create([
            'participant_id' => $participant->id,
            'invoice_number' => 'INV-1001',
            'status' => 'approved',
            'amount_cents' => 10000,
            'invoice_date' => $period->copy()->addDays(5)->toDateString(),
        ]);

        Invoice::create([
            'participant_id' => $participant->id,
            'invoice_number' => 'INV-1002',
            'status' => 'paid',
            'amount_cents' => 5000,
            'invoice_date' => $period->copy()->addDays(10)->toDateString(),
        ]);

        $response = $this->actingAs($participantUser)->get(route('portal.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('$350.00');
        $response->assertSee('$200.00');
    }

    public function test_login_redirects_each_role_to_their_dashboard(): void
    {
        $cases = [
            ['participant', 'participant-login@example.com', route('portal.dashboard'), ['Welcome back,', 'My Profile', 'Quarterly Budget']],
            ['worker', 'worker-login@example.com', route('portal.worker.dashboard'), ['Good morning,', 'Assigned Participant Chat', 'Assigned shifts today']],
            ['admin', 'admin-login@example.com', route('portal.admin.dashboard'), ['Self-Management Admin Dashboard', 'Admin Control Centre', 'Monitor participants']],
        ];

        foreach ($cases as [$role, $email, $dashboardRoute, $expectedStrings]) {
            $user = User::create([
                'name' => ucfirst($role).' Login',
                'email' => $email,
                'role' => $role,
                'status' => 'active',
                'mfa_enabled' => false,
                'password' => Hash::make('Password123!'),
                'password_changed_at' => now(),
            ]);

            if ($role === 'worker') {
                Worker::create([
                    'user_id' => $user->id,
                    'worker_number' => 'W-'.$user->id,
                    'first_name' => 'Worker',
                    'last_name' => 'Login',
                    'phone' => $user->phone,
                    'email' => $user->email,
                    'role_type' => 'worker',
                    'status' => 'active',
                    'onboarding_stage' => 6,
                ]);
            }

            $loginResponse = $this->post(route('portal.login.submit'), [
                'email' => $email,
                'password' => 'Password123!',
            ]);

            $loginResponse->assertRedirect($dashboardRoute);
            $this->assertAuthenticatedAs($user);

            $dashboardResponse = $this->get($dashboardRoute);
            $dashboardResponse->assertStatus(200);

            foreach ($expectedStrings as $expected) {
                $dashboardResponse->assertSee($expected);
            }

            Auth::logout();
            $this->assertGuest();
        }
    }

    public function test_unauthenticated_dashboard_redirects_to_login_page(): void
    {
        $response = $this->get(route('portal.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_visiting_login_is_redirected_to_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Redirected Admin',
            'email' => 'redirected-admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('portal.login'));

        $response->assertRedirect(route('portal.admin.dashboard'));
    }

    public function test_login_redirects_to_mfa_setup_when_required_for_participant_accounts(): void
    {
        PortalSetting::create([
            'key' => 'require_mfa',
            'value' => true,
        ]);

        $participant = User::create([
            'name' => 'MFA Required Participant',
            'email' => 'mfa-required@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->withSession(['_token' => csrf_token()])->post(route('portal.login.submit'), [
            '_token' => csrf_token(),
            'email' => 'mfa-required@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('portal.mfa.setup'));
        $this->assertAuthenticatedAs($participant);
    }

    public function test_login_does_not_redirect_to_mfa_setup_when_require_mfa_is_disabled(): void
    {
        PortalSetting::create([
            'key' => 'require_mfa',
            'value' => false,
        ]);

        $participant = User::create([
            'name' => 'MFA Disabled Participant',
            'email' => 'mfa-disabled@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->withSession(['_token' => csrf_token()])->post(route('portal.login.submit'), [
            '_token' => csrf_token(),
            'email' => 'mfa-disabled@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($participant);
    }

    public function test_root_redirects_authenticated_user_to_dashboard(): void
    {
        $worker = User::create([
            'name' => 'Root Worker',
            'email' => 'root-worker@example.com',
            'role' => 'worker',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        Worker::create([
            'user_id' => $worker->id,
            'worker_number' => 'W-'.$worker->id,
            'first_name' => 'Root',
            'last_name' => 'Worker',
            'phone' => $worker->phone,
            'email' => $worker->email,
            'role_type' => 'worker',
            'status' => 'active',
            'onboarding_stage' => 6,
        ]);

        $response = $this->actingAs($worker)->get('/');

        $response->assertRedirect(route('portal.worker.dashboard'));
    }

    public function test_root_redirects_authenticated_admin_to_admin_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Root Admin',
            'email' => 'root-admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertRedirect(route('portal.admin.dashboard'));
    }

    public function test_admin_can_create_admin_account(): void
    {
        $admin = User::create([
            'name' => 'Admin Creator',
            'email' => 'admin-creator@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)->post(route('portal.admin.users.store'), [
            'name' => 'Created Admin',
            'email' => 'created-admin@example.com',
            'phone' => '0411111111',
            'role' => 'admin',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect(route('portal.admin.users'));
        $this->assertDatabaseHas('users', [
            'email' => 'created-admin@example.com',
            'role' => 'admin',
        ]);
    }

    public function test_admin_user_creation_shows_inline_validation_feedback_on_invalid_submission(): void
    {
        $admin = User::create([
            'name' => 'Admin Creator',
            'email' => 'admin-creator-validation@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('portal.admin.users.create'))
            ->followingRedirects()
            ->post(route('portal.admin.users.store'), [
                'name' => '',
                'email' => '',
                'phone' => '',
                'role' => '',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertStatus(200);
        $response->assertSee('The name field is required.');
        $response->assertSee('The email field is required.');
        $response->assertSee('The role field is required.');
        $response->assertSee('The password field is required.');
    }

    public function test_non_admin_cannot_access_admin_user_creation_page(): void
    {
        $participant = User::create([
            'name' => 'Participant Only',
            'email' => 'participant-only@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($participant)->get(route('portal.admin.users.create'));

        $response->assertForbidden();
    }
}
