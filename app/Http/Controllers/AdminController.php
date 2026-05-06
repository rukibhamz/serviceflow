<?php

namespace App\Http\Controllers;

use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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

    public function saveSsoSettings(Request $request, SettingService $settings): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'sso_enabled' => ['nullable', 'boolean'],
            'sso_google_enabled' => ['nullable', 'boolean'],
            'sso_microsoft_enabled' => ['nullable', 'boolean'],
            'sso_github_enabled' => ['nullable', 'boolean'],
            'sso_atlassian_enabled' => ['nullable', 'boolean'],
            'sso_slack_enabled' => ['nullable', 'boolean'],
            'sso_google_client_id' => ['nullable', 'string', 'max:255'],
            'sso_google_client_secret' => ['nullable', 'string', 'max:255'],
            'sso_google_redirect' => ['nullable', 'url', 'max:255'],
            'sso_microsoft_client_id' => ['nullable', 'string', 'max:255'],
            'sso_microsoft_client_secret' => ['nullable', 'string', 'max:255'],
            'sso_microsoft_redirect' => ['nullable', 'url', 'max:255'],
            'sso_microsoft_tenant' => ['nullable', 'string', 'max:255'],
            'sso_github_client_id' => ['nullable', 'string', 'max:255'],
            'sso_github_client_secret' => ['nullable', 'string', 'max:255'],
            'sso_github_redirect' => ['nullable', 'url', 'max:255'],
            'sso_atlassian_client_id' => ['nullable', 'string', 'max:255'],
            'sso_atlassian_client_secret' => ['nullable', 'string', 'max:255'],
            'sso_atlassian_redirect' => ['nullable', 'url', 'max:255'],
            'sso_slack_client_id' => ['nullable', 'string', 'max:255'],
            'sso_slack_client_secret' => ['nullable', 'string', 'max:255'],
            'sso_slack_redirect' => ['nullable', 'url', 'max:255'],
        ]);

        $settings->set([
            'sso_enabled' => (bool) ($validated['sso_enabled'] ?? false),
            'sso_google_enabled' => (bool) ($validated['sso_google_enabled'] ?? false),
            'sso_microsoft_enabled' => (bool) ($validated['sso_microsoft_enabled'] ?? false),
            'sso_github_enabled' => (bool) ($validated['sso_github_enabled'] ?? false),
            'sso_atlassian_enabled' => (bool) ($validated['sso_atlassian_enabled'] ?? false),
            'sso_slack_enabled' => (bool) ($validated['sso_slack_enabled'] ?? false),
            'sso_google_client_id' => $validated['sso_google_client_id'] ?? null,
            'sso_google_client_secret' => $validated['sso_google_client_secret'] ?? null,
            'sso_google_redirect' => $validated['sso_google_redirect'] ?? null,
            'sso_microsoft_client_id' => $validated['sso_microsoft_client_id'] ?? null,
            'sso_microsoft_client_secret' => $validated['sso_microsoft_client_secret'] ?? null,
            'sso_microsoft_redirect' => $validated['sso_microsoft_redirect'] ?? null,
            'sso_microsoft_tenant' => $validated['sso_microsoft_tenant'] ?? null,
            'sso_github_client_id' => $validated['sso_github_client_id'] ?? null,
            'sso_github_client_secret' => $validated['sso_github_client_secret'] ?? null,
            'sso_github_redirect' => $validated['sso_github_redirect'] ?? null,
            'sso_atlassian_client_id' => $validated['sso_atlassian_client_id'] ?? null,
            'sso_atlassian_client_secret' => $validated['sso_atlassian_client_secret'] ?? null,
            'sso_atlassian_redirect' => $validated['sso_atlassian_redirect'] ?? null,
            'sso_slack_client_id' => $validated['sso_slack_client_id'] ?? null,
            'sso_slack_client_secret' => $validated['sso_slack_client_secret'] ?? null,
            'sso_slack_redirect' => $validated['sso_slack_redirect'] ?? null,
        ]);

        return back()->with('sso_saved', true);
    }

    public function saveMailSettings(Request $request, SettingService $settings): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'mail_mailer' => ['required', 'in:smtp,log,array'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['nullable', 'in:tls,ssl'],
            'mail_from_address' => ['nullable', 'email', 'max:255'],
            'mail_from_name' => ['nullable', 'string', 'max:255'],
            'mail_inbound_enabled' => ['nullable', 'boolean'],
            'mail_inbound_protocol' => ['nullable', 'in:imap,piped'],
            'mail_inbound_host' => ['nullable', 'string', 'max:255'],
            'mail_inbound_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_inbound_username' => ['nullable', 'string', 'max:255'],
            'mail_inbound_password' => ['nullable', 'string', 'max:255'],
            'mail_inbound_encryption' => ['nullable', 'in:tls,ssl,none'],
            'mail_inbound_folder' => ['nullable', 'string', 'max:255'],
        ]);

        $settings->set([
            'mail_mailer' => $validated['mail_mailer'],
            'mail_host' => $validated['mail_host'] ?? null,
            'mail_port' => $validated['mail_port'] ?? null,
            'mail_username' => $validated['mail_username'] ?? null,
            'mail_password' => $validated['mail_password'] ?? null,
            'mail_encryption' => $validated['mail_encryption'] ?? null,
            'mail_from_address' => $validated['mail_from_address'] ?? null,
            'mail_from_name' => $validated['mail_from_name'] ?? null,
            'mail_inbound_enabled' => (bool) ($validated['mail_inbound_enabled'] ?? false),
            'mail_inbound_protocol' => $validated['mail_inbound_protocol'] ?? 'imap',
            'mail_inbound_host' => $validated['mail_inbound_host'] ?? null,
            'mail_inbound_port' => $validated['mail_inbound_port'] ?? null,
            'mail_inbound_username' => $validated['mail_inbound_username'] ?? null,
            'mail_inbound_password' => $validated['mail_inbound_password'] ?? null,
            'mail_inbound_encryption' => $validated['mail_inbound_encryption'] ?? 'tls',
            'mail_inbound_folder' => $validated['mail_inbound_folder'] ?? 'INBOX',
        ]);

        return back()->with('mail_saved', true);
    }

    public function testMailSettings(Request $request, SettingService $settings): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'test_email' => ['required', 'email'],
        ]);

        try {
            config([
                'mail.default' => $settings->get('mail_mailer', config('mail.default')),
                'mail.mailers.smtp.host' => $settings->get('mail_host', config('mail.mailers.smtp.host')),
                'mail.mailers.smtp.port' => (int) $settings->get('mail_port', config('mail.mailers.smtp.port')),
                'mail.mailers.smtp.username' => $settings->get('mail_username', config('mail.mailers.smtp.username')),
                'mail.mailers.smtp.password' => $settings->get('mail_password', config('mail.mailers.smtp.password')),
                'mail.mailers.smtp.scheme' => $settings->get('mail_encryption', config('mail.mailers.smtp.scheme')),
                'mail.from.address' => $settings->get('mail_from_address', config('mail.from.address')),
                'mail.from.name' => $settings->get('mail_from_name', config('mail.from.name')),
            ]);

            Mail::raw('ServiceFlow SMTP test successful.', function ($message) use ($request) {
                $message->to($request->string('test_email')->toString())
                    ->subject('ServiceFlow Mail Test');
            });

            $imapResult = 'skipped';
            if ((bool) $settings->get('mail_inbound_enabled', false) && $settings->get('mail_inbound_protocol', 'imap') === 'imap') {
                if (function_exists('imap_open')) {
                    $enc = $settings->get('mail_inbound_encryption', 'tls');
                    $flags = '/imap' . ($enc === 'ssl' ? '/ssl' : ($enc === 'tls' ? '/tls' : '/notls'));
                    $mailbox = sprintf('{%s:%s%s}%s',
                        $settings->get('mail_inbound_host'),
                        $settings->get('mail_inbound_port', 993),
                        $flags,
                        $settings->get('mail_inbound_folder', 'INBOX')
                    );
                    $stream = @imap_open($mailbox, (string) $settings->get('mail_inbound_username'), (string) $settings->get('mail_inbound_password'));
                    if ($stream === false) {
                        throw new \RuntimeException('IMAP connect failed: ' . (imap_last_error() ?: 'unknown error'));
                    }
                    imap_close($stream);
                    $imapResult = 'ok';
                } else {
                    $imapResult = 'imap_extension_missing';
                }
            }

            return back()->with('mail_test_result', "SMTP test sent successfully. IMAP check: {$imapResult}.");
        } catch (\Throwable $e) {
            return back()->withErrors(['mail_test' => 'Mail test failed: ' . $e->getMessage()]);
        }
    }
}
