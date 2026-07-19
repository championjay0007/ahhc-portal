<?php

namespace Tests\Feature;

use App\Models\PortalSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_pwa_status_route_reports_disabled_when_setting_is_off(): void
    {
        PortalSetting::create([
            'key' => 'pwa_enabled',
            'value' => '0',
        ]);

        $response = $this->get('/portal/pwa-status');

        $response->assertOk();
        $response->assertExactJson(['enabled' => false]);
    }

    public function test_pwa_status_route_reports_enabled_when_setting_is_on(): void
    {
        PortalSetting::create([
            'key' => 'pwa_enabled',
            'value' => '1',
        ]);

        $response = $this->get('/portal/pwa-status');

        $response->assertOk();
        $response->assertExactJson(['enabled' => true]);
    }
}
