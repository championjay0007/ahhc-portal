<?php

namespace Tests\Feature;

use App\Models\PortalNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_notification_owner_can_open_notification_without_forbidden_error(): void
    {
        $user = User::factory()->create();
        $notification = PortalNotification::create([
            'user_id' => $user->id,
            'recipient_id' => $user->id,
            'title' => 'Test notification',
            'message' => 'Notification body',
            'type' => 'test',
            'channel' => 'in_app',
            'data' => ['url' => 'https://example.com'],
        ]);

        $this->actingAs($user);

        $response = $this->get(route('portal.notifications.show', $notification));

        $response->assertRedirect('https://example.com');
    }

    public function test_invoice_notification_open_does_not_change_invoice_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $participantUser = User::factory()->create(['role' => 'participant']);
        $participant = \App\Models\Participant::create([
            'user_id' => $participantUser->id,
            'participant_number' => 'P-3001',
            'first_name' => 'Invoice',
            'last_name' => 'Participant',
            'status' => 'active',
        ]);

        $invoice = \App\Models\Invoice::create([
            'participant_id' => $participant->id,
            'invoice_number' => 'INV-3001',
            'status' => 'submitted',
            'amount_cents' => 25000,
            'invoice_date' => now()->toDateString(),
            'service_date' => now()->subDay()->toDateString(),
            'due_date' => now()->addDays(14)->toDateString(),
            'invoice_file_path' => 'invoices/invoice-3001.pdf',
            'attachment_path' => 'invoices/invoice-3001.pdf',
            'attachment_disk' => 'local',
            'attachment_mime_type' => 'application/pdf',
        ]);

        $notification = PortalNotification::create([
            'user_id' => $admin->id,
            'recipient_id' => $admin->id,
            'participant_id' => $participant->id,
            'type' => 'invoice_submitted',
            'title' => 'Invoice submitted',
            'message' => 'A new invoice has been submitted for review.',
            'channel' => 'in_app',
            'data' => ['url' => route('portal.admin.invoices.review', $invoice)],
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('portal.notifications.show', $notification));

        $response->assertRedirect(route('portal.admin.invoices.show', $invoice));
        $this->assertSame('submitted', $invoice->refresh()->status);
    }

    public function test_notification_non_owner_is_redirected_instead_of_forbidden(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $notification = PortalNotification::create([
            'user_id' => $owner->id,
            'recipient_id' => $owner->id,
            'title' => 'Test notification',
            'message' => 'Notification body',
            'type' => 'test',
            'channel' => 'in_app',
            'data' => ['url' => 'https://example.com'],
        ]);

        $this->actingAs($viewer);

        $response = $this->get(route('portal.notifications.show', $notification));

        $response->assertRedirect(route('portal.dashboard'));
    }
}
