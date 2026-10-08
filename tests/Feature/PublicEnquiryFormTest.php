<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicEnquiryFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_renders_enquiry_submit_ui_with_success_modal(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('public.enquiry'), false);
        $response->assertSee('id="enquirySubmitBtn"', false);
        $response->assertSee('id="enquirySuccessModal"', false);
        $response->assertSee('Submitting your enquiry...', false);
    }

    public function test_dedicated_enquiry_page_displays_server_validation_errors(): void
    {
        $response = $this->followingRedirects()
            ->from(route('public.enquiry'))
            ->post(route('public.enquiries.store'), [
                'name' => 'Taylor Example',
                'email' => 'not-an-email',
                'role' => 'not-a-valid-role',
            ]);

        $response->assertOk()
            ->assertSee('Self-Management Enquiry')
            ->assertSee('Please correct the following and try again:')
            ->assertSee('The email field must be a valid email address.')
            ->assertSee('The selected role is invalid.')
            ->assertSee('The consent to contact field is required.')
            ->assertSee('value="Taylor Example"', false)
            ->assertSee('is-invalid', false);
    }

    public function test_enquiry_submits_when_message_is_omitted_or_empty(): void
    {
        foreach ([
            ['name' => 'Taylor Omitted', 'email' => 'omitted@example.com'],
            ['name' => 'Taylor Empty', 'email' => 'empty@example.com', 'message' => ''],
        ] as $submission) {
            $response = $this->from(route('public.enquiry'))
                ->post(route('public.enquiries.store'), $submission + [
                    'role' => 'participant',
                    'consent' => '1',
                ]);

            $response->assertRedirect(route('public.enquiry'))
                ->assertSessionHas('status');
        }

        $this->assertDatabaseHas('enquiries', [
            'email' => 'omitted@example.com',
            'message' => '',
        ]);
        $this->assertDatabaseHas('enquiries', [
            'email' => 'empty@example.com',
            'message' => '',
        ]);
    }
}
