<?php

namespace App\View\Composers;

use App\Services\SettingService;
use Illuminate\View\View;

class SettingsComposer
{
    public function __construct(private readonly SettingService $settings) {}

    public function compose(View $view): void
    {
        try {
            $view->with('appSettings', $this->settings->all());
            $view->with('cssVars', $this->settings->cssVars());
            $view->with('brandLogoUrl', $this->settings->logoUrl());
        } catch (\Throwable $e) {
            $view->with('appSettings', []);
            $view->with('cssVars', '');
            $view->with('brandLogoUrl', null);
        }
    }
}
