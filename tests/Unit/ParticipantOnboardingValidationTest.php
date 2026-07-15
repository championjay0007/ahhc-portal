<?php

namespace Tests\Unit;

use App\Http\Controllers\ParticipantOnboardingController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ParticipantOnboardingValidationTest extends TestCase
{
    public function test_only_profile_and_agreement_rules_are_required_for_completion(): void
    {
        $controller = new ParticipantOnboardingController();
        $reflection = new ReflectionMethod($controller, 'validationRulesForCurrentStep');
        $reflection->setAccessible(true);

        $step8Rules = $reflection->invoke($controller, 8);
        $this->assertArrayHasKey('preferred_name', $step8Rules);
        $this->assertArrayHasKey('phone', $step8Rules);
        $this->assertArrayHasKey('agreement_self_management', $step8Rules);
        $this->assertArrayHasKey('agreement_full_name', $step8Rules);
        $this->assertArrayNotHasKey('password', $step8Rules);
        $this->assertArrayNotHasKey('emergency_contact_name', $step8Rules);
        $this->assertArrayNotHasKey('support_first_name', $step8Rules);
        $this->assertArrayNotHasKey('document_type', $step8Rules);
    }
}
