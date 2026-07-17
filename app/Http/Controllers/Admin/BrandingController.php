<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PortalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BrandingController extends Controller
{
    public function edit()
    {
        $logoPath = PortalSetting::where('key', 'logo_path')->value('value');

        return view('portal.admin.branding.edit', [
            'logoPath' => $logoPath,
        ]);
    }

    protected function generateResizedIcons(string $sourcePath, string $iconsDir)
    {
        if (! file_exists($sourcePath)) {
            return;
        }

        $ext = strtolower(pathinfo($sourcePath, PATHINFO_EXTENSION));

        // If SVG, just copy to icon files (browsers accept SVGs for icons)
        if ($ext === 'svg') {
            copy($sourcePath, $iconsDir . DIRECTORY_SEPARATOR . 'icon-192.png');
            copy($sourcePath, $iconsDir . DIRECTORY_SEPARATOR . 'icon-512.png');
            copy($sourcePath, $iconsDir . DIRECTORY_SEPARATOR . 'apple-touch-icon.png');
            return;
        }

        $image = @imagecreatefromstring(file_get_contents($sourcePath));
        if (! $image) {
            return;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $sizes = [
            ['file' => 'icon-192.png', 'size' => 192],
            ['file' => 'icon-512.png', 'size' => 512],
            ['file' => 'apple-touch-icon.png', 'size' => 180],
        ];

        foreach ($sizes as $s) {
            $dst = imagecreatetruecolor($s['size'], $s['size']);
            // preserve transparency for PNGs
            imagealphablending($dst, false);
            imagesavealpha($dst, true);

            // calculate scale and center crop
            $scale = max($s['size'] / $width, $s['size'] / $height);
            $newW = (int)($width * $scale);
            $newH = (int)($height * $scale);

            $tmp = imagecreatetruecolor($newW, $newH);
            imagealphablending($tmp, false);
            imagesavealpha($tmp, true);
            imagecopyresampled($tmp, $image, 0, 0, 0, 0, $newW, $newH, $width, $height);

            // center crop to square
            $cropX = (int)(($newW - $s['size']) / 2);
            $cropY = (int)(($newH - $s['size']) / 2);
            imagecopy($dst, $tmp, 0, 0, $cropX, $cropY, $s['size'], $s['size']);

            $outPath = $iconsDir . DIRECTORY_SEPARATOR . $s['file'];
            imagepng($dst, $outPath);
            imagedestroy($dst);
            imagedestroy($tmp);
        }

        imagedestroy($image);
    }

    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $path = $file->store('branding', 'public');

            // Save relative path in settings
            PortalSetting::updateOrCreate(
                ['key' => 'logo_path'],
                ['value' => $path]
            );
            // Also save a pwa_icon_path for the manifest to use
            PortalSetting::updateOrCreate(
                ['key' => 'pwa_icon_path'],
                ['value' => $path]
            );

            // Generate resized public icons (192x192, 512x512, apple-touch-icon)
            try {
                $storagePath = Storage::disk('public')->path($path);
                $iconsDir = public_path('icons');
                if (! is_dir($iconsDir)) {
                    mkdir($iconsDir, 0755, true);
                }

                $this->generateResizedIcons($storagePath, $iconsDir);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return redirect()->route('portal.admin.branding.edit')
            ->with('status', 'Logo updated successfully.');
    }
}
