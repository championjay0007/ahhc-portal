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
        }

        return redirect()->route('portal.admin.branding.edit')
            ->with('status', 'Logo updated successfully.');
    }
}
