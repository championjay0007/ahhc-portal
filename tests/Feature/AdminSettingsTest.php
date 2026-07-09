<?php

namespace Tests\Feature;

use App\Models\PortalSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_persisted_settings(): void
    {
        $admin = User::create([
            'name' => 'Admin Settings Owner',
            'email' => 'admin-settings-owner@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => true,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('portal.admin.settings'))
            ->post(route('portal.admin.settings.update'), [
                'organization_name' => 'AHHC Care Services',
                'support_email' => 'care@ahhc.example.com',
                'default_user_role' => 'worker',
                'require_mfa' => true,
                'report_export_emails' => true,
                'incident_alerts' => false,
                'session_lifetime' => 240,
            ]);

        $response->assertRedirect(route('portal.admin.settings'));
        $response->assertSessionHas('status', 'Settings updated.');

        $ajaxResponse = $this->actingAs($admin)
            ->from(route('portal.admin.settings'))
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->postJson(route('portal.admin.settings.update'), [
                'organization_name' => 'AHHC Care Services',
                'support_email' => 'care@ahhc.example.com',
                'default_user_role' => 'worker',
                'require_mfa' => true,
                'report_export_emails' => true,
                'incident_alerts' => false,
            ]);

        $ajaxResponse->assertOk();
        $ajaxResponse->assertJsonPath('message', 'Settings updated.');

        $this->assertSame('AHHC Care Services', PortalSetting::where('key', 'organization_name')->value('value'));
        $this->assertSame('care@ahhc.example.com', PortalSetting::where('key', 'support_email')->value('value'));
        $this->assertSame('worker', PortalSetting::where('key', 'default_user_role')->value('value'));
        $this->assertTrue((bool) PortalSetting::where('key', 'require_mfa')->value('value'));
        $this->assertTrue((bool) PortalSetting::where('key', 'report_export_emails')->value('value'));
        $this->assertFalse((bool) PortalSetting::where('key', 'incident_alerts')->value('value'));
        $this->assertSame(240, (int) PortalSetting::where('key', 'session_lifetime')->value('value'));

        $followUp = $this->actingAs($admin)->get(route('portal.admin.settings'));
        $followUp->assertStatus(200);
        $followUp->assertSee('AHHC Care Services');
        $followUp->assertSee('care@ahhc.example.com');
        $followUp->assertSee('Worker');
    }

    public function test_admin_can_generate_vapid_keys_and_persist_them(): void
    {
        $admin = User::create([
            'name' => 'Admin VAPID Generator',
            'email' => 'admin-vapid-generator@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => true,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->from(route('portal.admin.settings'))
            ->post(route('portal.admin.settings.generate_vapid_keys'), [
                'vapid_subject' => 'mailto:admin@example.com',
            ]);

        $response->assertRedirect(route('portal.admin.settings'));
        $response->assertSessionHas('status', 'VAPID keys generated successfully.');

        $this->assertNotEmpty(PortalSetting::where('key', 'vapid_public_key')->value('value'));
        $this->assertNotEmpty(PortalSetting::where('key', 'vapid_private_key')->value('value'));
        $this->assertSame('mailto:admin@example.com', PortalSetting::where('key', 'vapid_subject')->value('value'));
    }

    public function test_admin_can_upload_pwa_icon_and_update_manifest(): void
    {
        Storage::fake('public');

        $admin = User::create([
            'name' => 'Admin Settings Icon',
            'email' => 'admin-settings-icon@example.com',
            'role' => 'admin',
            'status' => 'active',
            'mfa_enabled' => true,
            'password' => Hash::make('Password123!'),
            'password_changed_at' => now(),
        ]);

        $icon = UploadedFile::fake()->image('pwa-icon.png', 512, 512);

        $response = $this->actingAs($admin)
            ->from(route('portal.admin.settings'))
            ->post(route('portal.admin.settings.update'), [
                'organization_name' => 'AHHC Care Services',
                'support_email' => 'care@ahhc.example.com',
                'default_user_role' => 'worker',
                'require_mfa' => true,
                'report_export_emails' => true,
                'incident_alerts' => false,
                'pwa_icon' => $icon,
            ]);

        $response->assertRedirect(route('portal.admin.settings'));
        $response->assertSessionHas('status', 'Settings updated.');

        $pwaIconPath = PortalSetting::where('key', 'pwa_icon_path')->value('value');
        $this->assertNotEmpty($pwaIconPath);
        Storage::disk('public')->assertExists($pwaIconPath);

        $manifest = json_decode(file_get_contents(public_path('manifest.json')), true);
        $this->assertSame('/storage/'.ltrim($pwaIconPath, '/'), $manifest['icons'][0]['src']);
        $this->assertSame('/storage/'.ltrim($pwaIconPath, '/'), $manifest['icons'][1]['src']);
        $this->assertSame('/storage/'.ltrim($pwaIconPath, '/'), $manifest['icons'][2]['src']);
    }
}
