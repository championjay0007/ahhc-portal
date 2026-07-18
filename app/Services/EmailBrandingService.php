<?php

namespace App\Services;

use App\Models\PortalSetting;
use Illuminate\Support\Str;

class EmailBrandingService
{
    public static function logoUrl(?string $logo = null): string
    {
        if (! empty($logo)) {
            if (! static::isRemoteLogo($logo) && static::localLogoFile($logo) === null) {
                return asset('storage/branding/logo.jpg');
            }

            return static::normalizeLogoUrl($logo);
        }

        $logoPath = PortalSetting::where('key', 'logo_path')->value('value');

        if (! empty($logoPath) && static::localLogoFile($logoPath) !== null) {
            return static::normalizeLogoUrl($logoPath);
        }

        return 'https://allegiancehearthomecare.com.au/images/logo.jpg';
    }

    protected static function localLogoFile(string $logo): ?string
    {
        if (static::isRemoteLogo($logo)) {
            return null;
        }

        $file = storage_path('app/public/'.ltrim(preg_replace('#^storage/[\\/]*#', '', $logo), '/'));

        return file_exists($file) ? $file : null;
    }

    public static function logoSource(?string $logo = null): ?string
    {
        if (! empty($logo) && ! static::isRemoteLogo($logo)) {
            $file = storage_path('app/public/'.ltrim(preg_replace('#^storage/[\\/]*#', '', $logo), '/'));
            if (file_exists($file)) {
                return $file;
            }
        }

        $logoPath = PortalSetting::where('key', 'logo_path')->value('value');
        if (! empty($logoPath)) {
            $file = storage_path('app/public/'.ltrim(preg_replace('#^storage/[\\/]*#', '', $logoPath), '/'));
            if (file_exists($file)) {
                return $file;
            }
        }

        $default = storage_path('app/public/branding/logo.jpg');
        return file_exists($default) ? $default : null;
    }

    protected static function isRemoteLogo(string $logo): bool
    {
        return Str::startsWith($logo, ['http://', 'https://', 'data:']);
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
