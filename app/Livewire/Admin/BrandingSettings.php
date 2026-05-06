<?php

namespace App\Livewire\Admin;

use App\Services\SettingService;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class BrandingSettings extends Component
{
    use WithFileUploads;

    #[Rule('required|string|max:80')]
    public string $brandName = '';

    public string $themePreset = 'blue';
    public string $themePrimary = '#1a4fa0';
    public string $themeAccent  = '#f97316';

    public $logo = null;          // uploaded file (temp)
    public ?string $currentLogo = null;  // existing stored path

    public $favicon = null;          // uploaded favicon (temp)
    public ?string $currentFavicon = null;  // existing stored path

    public bool $saved = false;

    public function mount(SettingService $settings): void
    {
        $all = $settings->all();
        $this->brandName    = $all['brand_name']    ?? 'ServiceFlow';
        $this->themePreset  = $all['theme_preset']  ?? 'blue';
        $this->themePrimary = $all['theme_primary'] ?? '#1a4fa0';
        $this->themeAccent  = $all['theme_accent']  ?? '#f97316';
        $this->currentLogo  = $settings->logoUrl();
        $this->currentFavicon = $settings->faviconUrl();
    }

    /** When a preset is selected, auto-fill the hex fields (unless custom). */
    public function updatedThemePreset(string $value): void
    {
        $presets = SettingService::presets();
        if ($value !== 'custom' && isset($presets[$value])) {
            $this->themePrimary = $presets[$value]['primary'];
            $this->themeAccent  = $presets[$value]['accent'];
        }
    }

    public function save(SettingService $settings): void
    {
        $this->validate();

        // Handle logo upload
        if ($this->logo !== null) {
            $this->validate(['logo' => 'image|max:2048']);
            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $this->logo;
            $this->currentLogo = $settings->uploadLogo($file);
            $this->logo = null;
        }

        // Handle favicon upload
        if ($this->favicon !== null) {
            $this->validate(['favicon' => 'image|mimes:ico,png,jpg,jpeg,svg|max:512']);
            /** @var \Illuminate\Http\UploadedFile $file */
            $file = $this->favicon;
            $this->currentFavicon = $settings->uploadFavicon($file);
            $this->favicon = null;
        }

        $settings->set([
            'brand_name'    => $this->brandName,
            'theme_preset'  => $this->themePreset,
            'theme_primary' => $this->themePrimary,
            'theme_accent'  => $this->themeAccent,
        ]);

        $this->saved = true;

        // Dispatch browser event so Alpine can patch <style id="theme-vars"> immediately
        $this->dispatch('branding-saved', primary: $this->themePrimary, accent: $this->themeAccent);
    }

    public function removeLogo(SettingService $settings): void
    {
        $path = $settings->get('brand_logo');
        if ($path) {
            \Illuminate\Support\Facades\Storage::disk('uploads')->delete($path);
        }
        $settings->set(['brand_logo' => null]);
        $this->currentLogo = null;
    }

    public function removeFavicon(SettingService $settings): void
    {
        $path = $settings->get('brand_favicon');
        if ($path) {
            \Illuminate\Support\Facades\Storage::disk('uploads')->delete($path);
        }
        $settings->set(['brand_favicon' => null]);
        $this->currentFavicon = null;
    }

    public function render(): \Illuminate\View\View
    {
        $rendered = view('livewire.admin.branding-settings', [
            'presets' => SettingService::presets(),
        ]);
        // Reset saved flag after render so flash doesn't reappear on next re-render
        $this->saved = false;
        return $rendered;
    }
}
