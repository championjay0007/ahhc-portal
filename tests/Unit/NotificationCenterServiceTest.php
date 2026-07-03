<?php

namespace Tests\Unit;

use App\Models\Enquiry;
use App\Models\Participant;
use App\Models\PortalNotification;
use App\Models\User;
use App\Services\NotificationCenterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_onboarding_submitted_notification_is_created_with_review_url(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => bcrypt('Password123!'),
            'password_changed_at' => now(),
        ]);

        $participantUser = User::create([
            'name' => 'Participant User',
            'email' => 'participant-user@example.com',
            'role' => 'participant',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => bcrypt('Password123!'),
            'password_changed_at' => now(),
        ]);

        $participant = Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-100',
            'first_name' => 'Test',
            'last_name' => 'Participant',
            'status' => Participant::STATUS_PENDING_ADMIN_REVIEW,
            'email' => 'participant@example.com',
            'phone' => '0400000000',
            'consent_to_share' => true,
            'budget_limit_cents' => 0,
            'current_budget_used_cents' => 0,
        ]);

        NotificationCenterService::send('onboarding_submitted', $admin->id, [
            'participant_id' => $participant->id,
            'title' => 'Onboarding submitted',
            'message' => "{$participant->first_name} {$participant->last_name} submitted onboarding for review.",
        ]);

        $notification = PortalNotification::latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame('onboarding_submitted', $notification->type);
        $this->assertSame('Onboarding submitted', $notification->title);
        $this->assertSame("{$participant->first_name} {$participant->last_name} submitted onboarding for review.", $notification->message);
        $this->assertSame($participant->id, $notification->participant_id);
        $this->assertArrayHasKey('url', $notification->data);
        $this->assertStringContainsString(route('portal.admin.participants.show', $participant), $notification->data['url']);
    }

    public function test_new_enquiry_notification_creates_admin_enquiry_url(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => false,
            'password' => bcrypt('Password123!'),
            'password_changed_at' => now(),
        ]);

        $enquiry = Enquiry::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '0400123456',
            'role' => 'consumer',
            'support_at_home_status' => 'yes',
            'message' => 'I need support.',
            'consent' => true,
            'status' => 'new',
        ]);

        NotificationCenterService::send('new_enquiry', $admin->id, [
            'title' => 'New Public Enquiry',
            'message' => "{$enquiry->name} submitted an enquiry for AHHC Self-Management Support.",
            'data' => ['enquiry_id' => $enquiry->id],
        ]);

        $notification = PortalNotification::latest()->first();

        $this->assertNotNull($notification);
        $this->assertSame('new_enquiry', $notification->type);
        $this->assertArrayHasKey('url', $notification->data);
        $this->assertSame($enquiry->id, $notification->data['reference_id']);
        $this->assertStringContainsString(route('portal.admin.enquiries.show', $enquiry), $notification->data['url']);
    }
}
