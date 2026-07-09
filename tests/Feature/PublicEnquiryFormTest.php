<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicEnquiryFormTest extends TestCase
{
    public function test_welcome_page_renders_enquiry_submit_ui_with_success_modal(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('id="enquirySubmitBtn"', false);
        $response->assertSee('id="enquirySuccessModal"', false);
        $response->assertSee('Submitting your enquiry...', false);
    }
}
