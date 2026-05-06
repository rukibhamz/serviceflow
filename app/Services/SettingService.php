<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    private const CACHE_KEY = 'app_settings';
    private const CACHE_TTL = 3600; // 1 hour

    /** Retrieve all settings as an associative array (cached). */
    public function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return Setting::all()->pluck('value', 'key')->toArray();
        });
    }

    /** Get a single setting value with an optional default. */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /** Persist a batch of settings and bust the cache. */
    public function set(array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Handle logo upload, save to uploads disk, persist the path, and return it.
     */
    public function uploadLogo(\Illuminate\Http\UploadedFile $file): string
    {
        // Delete old logo if exists
        $old = $this->get('brand_logo');
        if ($old && Storage::disk('uploads')->exists($old)) {
            Storage::disk('uploads')->delete($old);
        }

        $path = $file->store('logos', 'uploads');
        $this->set(['brand_logo' => $path]);

        return $path;
    }

    /** Return the public URL for the stored logo. */
    public function logoUrl(): ?string
    {
        $path = $this->get('brand_logo');
        if (!$path) return null;
        return Storage::disk('uploads')->url($path);
    }

    /**
     * Handle favicon upload, save to uploads disk, persist the path, and return it.
     */
    public function uploadFavicon(\Illuminate\Http\UploadedFile $file): string
    {
        // Delete old favicon if exists
        $old = $this->get('brand_favicon');
        if ($old && Storage::disk('uploads')->exists($old)) {
            Storage::disk('uploads')->delete($old);
        }

        $path = $file->storeAs('favicons', 'favicon.' . $file->getClientOriginalExtension(), 'uploads');
        $this->set(['brand_favicon' => $path]);

        return $path;
    }

    /** Return the public URL for the stored favicon, falling back to the default. */
    public function faviconUrl(): string
    {
        $path = $this->get('brand_favicon');
        if ($path && Storage::disk('uploads')->exists($path)) {
            return Storage::disk('uploads')->url($path);
        }
        return asset('favicon.ico');
    }

    /**
     * Build CSS custom property overrides for the current theme,
     * to be injected inline into the <head>.
     */
    public function cssVars(): string
    {
        $settings = $this->all();
        $primary = $settings['theme_primary'] ?? '#1a4fa0';
        $accent  = $settings['theme_accent']  ?? '#f97316';

        return ":root { --brand: {$primary}; --brand-lt: {$primary}cc; --brand-dim: {$primary}33; --accent: {$accent}; }";
    }

    /** Pre-defined theme presets. */
    public static function presets(): array
    {
        return [
            'blue'        => ['label' => 'ServiceFlow Blue',  'primary' => '#1a4fa0', 'accent' => '#f97316'],
            'crimson_ash' => ['label' => 'Crimson & Ash',     'primary' => '#e12621', 'accent' => '#54565b'],
            'charcoal'    => ['label' => 'Charcoal & Red',    'primary' => '#4A4A4A', 'accent' => '#E53935'],
            'green'       => ['label' => 'Forest Green',      'primary' => '#166534', 'accent' => '#f59e0b'],
            'midnight'    => ['label' => 'Midnight',          'primary' => '#1e1b4b', 'accent' => '#a78bfa'],
            'slate'       => ['label' => 'Cool Slate',        'primary' => '#334155', 'accent' => '#38bdf8'],
            'rose'        => ['label' => 'Rose & Gold',       'primary' => '#9f1239', 'accent' => '#d97706'],
            'custom'      => ['label' => 'Custom…',           'primary' => null,      'accent' => null],
        ];
    }
}
