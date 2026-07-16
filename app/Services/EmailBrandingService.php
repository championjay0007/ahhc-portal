<?php

namespace App\Services;

use App\Models\PortalSetting;
use Illuminate\Support\Str;

class EmailBrandingService
{
    public static function logoUrl(?string $logo = null): string
    {
        if (! empty($logo)) {
            return static::normalizeLogoUrl($logo);
        }

        $logoPath = PortalSetting::where('key', 'logo_path')->value('value');

        if (! empty($logoPath)) {
            return static::normalizeLogoUrl($logoPath);
        }

        return asset('storage/branding/logo.jpg');
    }

    protected static function normalizeLogoUrl(string $logo): string
    {
        $logo = trim($logo);

        if (Str::startsWith($logo, ['http://', 'https://', 'data:'])) {
            return $logo;
        }

        $logoPath = ltrim($logo, '/');

        if (! Str::startsWith($logoPath, 'storage/')) {
            $logoPath = 'storage/' . $logoPath;
        }

        return asset($logoPath);
    }
}
