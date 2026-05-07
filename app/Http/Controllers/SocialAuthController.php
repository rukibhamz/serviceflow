<?php

namespace App\Http\Controllers;

use App\Models\SocialAccount;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Supported providers and their display names.
     */
    private const PROVIDERS = [
        'google'     => 'Google',
        'github'     => 'GitHub',
        'microsoft'  => 'Microsoft',
        'atlassian'  => 'Atlassian',
        'slack'      => 'Slack',
    ];

    /**
     * Map route provider names to Socialite driver names.
     */
    private const DRIVER_MAP = [
        'microsoft' => 'azure',
        'slack'     => 'slack-openid',
    ];

    private const PROVIDER_CONFIG_MAP = [
        'google' => 'google',
        'github' => 'github',
        'microsoft' => 'azure',
        'atlassian' => 'atlassian',
        'slack' => 'slack-openid',
    ];

    /**
     * Redirect to the provider's OAuth page.
     */
    public function redirect(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        $driver = $this->resolveDriver($provider);

        return Socialite::driver($driver)->redirect();
    }

    /**
     * Handle the OAuth callback from the provider.
     */
    public function callback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        $driver = $this->resolveDriver($provider);

        try {
            $socialUser = Socialite::driver($driver)->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'Authentication failed. Please try again.']);
        }

        // Find existing social account link
        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            // Update tokens
            $socialAccount->update([
                'token'         => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
            ]);

            $user = $socialAccount->user;
        } else {
            // Find user by email or create new one
            $user = User::where('email', $socialUser->getEmail())->first();

            if (! $user) {
                $user = User::create([
                    'name'     => $socialUser->getName() ?? $socialUser->getNickname() ?? 'User',
                    'email'    => $socialUser->getEmail(),
                    'password' => bcrypt(Str::random(32)),
                    'role'     => 'user', // default portal user
                ]);
            }

            // Link the social account
            SocialAccount::create([
                'user_id'       => $user->id,
                'provider'      => $provider,
                'provider_id'   => $socialUser->getId(),
                'token'         => $socialUser->token,
                'refresh_token' => $socialUser->refreshToken,
            ]);
        }

        if (! $user->is_active) {
            return redirect()->route('login')
                ->withErrors(['sso' => 'Your account has been deactivated. Please contact support.']);
        }

        Auth::login($user, true);

        return $this->redirectToDashboard($user);
    }

    /**
     * Redirect user to their appropriate dashboard based on role.
     */
    private function redirectToDashboard(User $user): RedirectResponse
    {
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('manager') || $user->role === 'manager') {
            return redirect()->route('manager.dashboard');
        }

        if ($user->hasRole('team_lead') || $user->role === 'team_lead') {
            return redirect()->route('team-lead.dashboard');
        }

        if ($user->hasRole('agent') || $user->role === 'agent') {
            return redirect()->route('agent.dashboard');
        }

        return redirect()->route('portal.index');
    }

    /**
     * Resolve the Socialite driver name from the route provider name.
     */
    private function resolveDriver(string $provider): string
    {
        return self::DRIVER_MAP[$provider] ?? $provider;
    }

    /**
     * Validate the provider is supported.
     */
    private function validateProvider(string $provider): void
    {
        if (! array_key_exists($provider, self::PROVIDERS)) {
            abort(404, "SSO provider '{$provider}' is not supported.");
        }

        $settings = app(SettingService::class);
        if (! (bool) $settings->get('sso_enabled', true)) {
            abort(403, 'Single Sign-On is disabled.');
        }

        if (! (bool) $settings->get('sso_' . $provider . '_enabled', true)) {
            abort(403, self::PROVIDERS[$provider] . ' SSO is disabled.');
        }

        $configKey = self::PROVIDER_CONFIG_MAP[$provider] ?? $provider;
        if (! config("services.{$configKey}.client_id")) {
            abort(403, self::PROVIDERS[$provider] . ' SSO is not configured.');
        }
    }
}
