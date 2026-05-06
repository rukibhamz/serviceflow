<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function saveBranding(Request $request, SettingService $settings): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'brand_name'    => 'required|string|max:80',
            'theme_preset'  => 'required|string',
            'theme_primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme_accent'  => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        // Handle logo upload
        if ($request->hasFile('brand_logo')) {
            $request->validate(['brand_logo' => 'image|max:2048']);
            $settings->uploadLogo($request->file('brand_logo'));
        }

        // Handle logo removal
        if ($request->input('remove_logo') === '1') {
            $path = $settings->get('brand_logo');
            if ($path) {
                \Illuminate\Support\Facades\Storage::disk('uploads')->delete($path);
            }
            $settings->set(['brand_logo' => null]);
        }

        // Handle favicon upload
        if ($request->hasFile('brand_favicon')) {
            $request->validate(['brand_favicon' => 'file|mimes:ico,png,jpg,jpeg,svg|max:512']);
            $settings->uploadFavicon($request->file('brand_favicon'));
        }

        // Handle favicon removal
        if ($request->input('remove_favicon') === '1') {
            $path = $settings->get('brand_favicon');
            if ($path) {
                \Illuminate\Support\Facades\Storage::disk('uploads')->delete($path);
            }
            $settings->set(['brand_favicon' => null]);
        }

        $settings->set([
            'brand_name'    => $data['brand_name'],
            'theme_preset'  => $data['theme_preset'],
            'theme_primary' => $data['theme_primary'],
            'theme_accent'  => $data['theme_accent'],
        ]);

        return back()->with('branding_saved', true);
    }
}
