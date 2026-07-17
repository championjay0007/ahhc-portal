<?php

namespace App\Http\Controllers;

use App\Models\PortalSetting;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ManifestController extends BaseController
{
    public function show(Request $request)
    {
        $defaults = [
            'website_name' => 'AHHC Portal',
            'website_description' => 'Manage participants, workers, invoices, approvals, documents, and compliance in one secure portal.',
            'primary_color' => '#0d6efd',
            'dashboard_primary_color' => '#0E3863',
            'logo_path' => null,
            'pwa_icon_path' => null,
        ];

        try {
            if (! \Illuminate\Support\Facades\Schema::hasTable('portal_settings')) {
                $settings = $defaults;
            } else {
                $stored = PortalSetting::query()->pluck('value', 'key')->all();
                $settings = array_replace($defaults, $stored);
            }
        } catch (\Throwable $e) {
            report($e);
            $settings = $defaults;
        }

        $icon192 = null;
        $icon512 = null;
        $maskable = null;

        if (! empty($settings['pwa_icon_path'])) {
            $icon192 = asset('storage/' . ltrim($settings['pwa_icon_path'], '/'));
            $icon512 = $icon192;
            $maskable = $icon192;
        } elseif (! empty($settings['logo_path'])) {
            $icon192 = asset('storage/' . ltrim($settings['logo_path'], '/'));
            $icon512 = $icon192;
            $maskable = $icon192;
        } else {
            $icon192 = asset('icons/icon-192.png');
            $icon512 = asset('icons/icon-512.png');
            $maskable = asset('icons/icon-512.png');
        }

        $manifest = [
            'name' => $settings['website_name'] ?? 'Allegiance Heart & Home Care Portal',
            'short_name' => substr($settings['website_name'] ?? 'AHHC', 0, 12),
            'description' => $settings['website_description'] ?? '',
            'start_url' => '/portal/dashboard?source=pwa',
            'scope' => '/portal/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => $settings['primary_color'] ?? '#0d6efd',
            'theme_color' => $settings['dashboard_primary_color'] ?? ($settings['primary_color'] ?? '#0d6efd'),
            'icons' => [
                [
                    'src' => $icon192,
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => $icon512,
                    'sizes' => '512x512',
                    'type' => 'image/png'
                ],
                [
                    'src' => $maskable,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ]
            ]
        ];

        return response()->json($manifest, 200, ['Content-Type' => 'application/manifest+json']);
    }
}
