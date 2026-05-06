<?php

namespace App\Providers;

use App\Events\CommentAdded;
use App\Events\SlaBreached;
use App\Events\TicketCreated;
use App\Events\TicketUpdated;
use App\Listeners\RunAutomationEngine;
use App\Models\KnowledgeArticle;
use App\Models\Ticket;
use App\Observers\KnowledgeArticleObserver;
use App\Observers\TicketObserver;
use App\Services\SettingService;
use App\View\Composers\SettingsComposer;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->applySsoConfigOverrides();
        $this->applyMailConfigOverrides();

        Ticket::observe(TicketObserver::class);
        KnowledgeArticle::observe(KnowledgeArticleObserver::class);

        // Apply branding settings to all views
        View::composer('*', SettingsComposer::class);

        // Register SocialiteProviders event listeners for Azure and Atlassian
        Event::listen(SocialiteWasCalled::class, \SocialiteProviders\Azure\AzureExtendSocialite::class . '@handle');
        Event::listen(SocialiteWasCalled::class, \SocialiteProviders\Atlassian\AtlassianExtendSocialite::class . '@handle');

        // Wire automation engine into all registered trigger events
        foreach ([TicketCreated::class, TicketUpdated::class, CommentAdded::class, SlaBreached::class] as $eventClass) {
            Event::listen($eventClass, RunAutomationEngine::class);
        }

        // Fix for subdirectory deployment: Livewire routes
        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)->middleware('web')->name('livewire.update');
        });

        Livewire::setScriptRoute(function ($handle) {
            return Route::get('/livewire/livewire.js', $handle)->middleware('web')->name('livewire.js');
        });
    }

    private function applySsoConfigOverrides(): void
    {
        try {
            $settings = app(SettingService::class);

            $providerMap = [
                'google' => 'google',
                'microsoft' => 'azure',
                'github' => 'github',
                'atlassian' => 'atlassian',
                'slack' => 'slack-openid',
            ];

            foreach ($providerMap as $settingPrefix => $configKey) {
                $clientId = $settings->get("sso_{$settingPrefix}_client_id");
                $clientSecret = $settings->get("sso_{$settingPrefix}_client_secret");
                $redirect = $settings->get("sso_{$settingPrefix}_redirect");

                if ($clientId) {
                    Config::set("services.{$configKey}.client_id", $clientId);
                }
                if ($clientSecret) {
                    Config::set("services.{$configKey}.client_secret", $clientSecret);
                }
                if ($redirect) {
                    Config::set("services.{$configKey}.redirect", $redirect);
                }
            }

            $microsoftTenant = $settings->get('sso_microsoft_tenant');
            if ($microsoftTenant) {
                Config::set('services.azure.tenant', $microsoftTenant);
            }
        } catch (\Throwable) {
            // Ignore if settings are unavailable during early boot.
        }
    }

    private function applyMailConfigOverrides(): void
    {
        try {
            $settings = app(SettingService::class);

            $mailer = $settings->get('mail_mailer');
            if ($mailer) {
                Config::set('mail.default', $mailer);
            }

            $smtpMap = [
                'mail_host' => 'mail.mailers.smtp.host',
                'mail_port' => 'mail.mailers.smtp.port',
                'mail_username' => 'mail.mailers.smtp.username',
                'mail_password' => 'mail.mailers.smtp.password',
                'mail_encryption' => 'mail.mailers.smtp.scheme',
                'mail_from_address' => 'mail.from.address',
                'mail_from_name' => 'mail.from.name',
            ];

            foreach ($smtpMap as $settingKey => $configKey) {
                $value = $settings->get($settingKey);
                if ($value !== null && $value !== '') {
                    Config::set($configKey, $value);
                }
            }
        } catch (\Throwable) {
            // Ignore if settings are unavailable during early boot.
        }
    }
}
