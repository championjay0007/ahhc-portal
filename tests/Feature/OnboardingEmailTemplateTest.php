<?php

namespace Tests\Feature;

use Tests\TestCase;

class OnboardingEmailTemplateTest extends TestCase
{
    public function test_onboarding_status_template_renders_polished_copy(): void
    {
        $html = view('mail.onboarding-status', [
            'title' => 'Your onboarding is underway',
            'greeting' => 'Hello Jordan,',
            'intro' => 'We\'ve received your onboarding details.',
            'body' => 'Your application is now under review by our team.',
            'ctaLabel' => 'View your status',
            'ctaUrl' => 'https://example.test/onboarding',
            'secondaryLabel' => 'Need help?',
            'secondaryUrl' => 'https://example.test/support',
            'organization' => 'AHHC Portal',
        ])->render();

        $decodedHtml = html_entity_decode($html, ENT_QUOTES, 'UTF-8');

        $this->assertStringContainsString('Your onboarding is underway', $decodedHtml);
        $this->assertStringContainsString('We\'ve received your onboarding details.', $decodedHtml);
        $this->assertStringContainsString('View your status', $decodedHtml);
    }
}
