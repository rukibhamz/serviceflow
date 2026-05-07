<?php

namespace App\Http\Controllers;

use App\Models\SlaTimer;
use App\Models\Team;
use App\Models\SlaPolicy;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserInvitation;
use App\Services\Tenant\TenantProvisioner;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function saveSlaPolicy(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'editing_id' => 'nullable|integer|exists:sla_policies,id',
            'name' => 'required|string|max:255',
            'priority' => 'required|in:low,medium,high,critical,urgent',
            'ticket_type' => 'nullable|in:incident,service_request,problem,change',
            'response_minutes' => 'required|integer|min:1',
            'resolution_minutes' => 'required|integer|min:1',
            'business_hours_only' => 'nullable|boolean',
            'is_default' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $ticketType = $data['ticket_type'] ?? null;
        $isDefault = (bool) ($data['is_default'] ?? false);

        $payload = [
            'name' => $data['name'],
            'priority' => $data['priority'],
            'ticket_type' => $ticketType ?: null,
            'response_minutes' => $data['response_minutes'],
            'resolution_minutes' => $data['resolution_minutes'],
            'business_hours' => (bool) ($data['business_hours_only'] ?? false)
                ? ['start' => '09:00', 'end' => '17:00', 'days' => [1, 2, 3, 4, 5]]
                : null,
            'is_default' => $isDefault,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ];

        if ($isDefault) {
            SlaPolicy::where('priority', $data['priority'])
                ->where('ticket_type', $payload['ticket_type'])
                ->where('id', '!=', $data['editing_id'] ?? 0)
                ->update(['is_default' => false]);
        }

        if (! empty($data['editing_id'])) {
            SlaPolicy::findOrFail($data['editing_id'])->update($payload);
            return redirect()->route('admin.sla')->with('success', 'SLA policy updated.');
        }

        SlaPolicy::create($payload);
        return redirect()->route('admin.sla')->with('success', 'SLA policy created.');
    }

    public function toggleSlaPolicy(int $policy): \Illuminate\Http\RedirectResponse
    {
        $policy = SlaPolicy::findOrFail($policy);
        $policy->is_active = ! $policy->is_active;
        $policy->save();

        return redirect()->route('admin.sla')->with('success', 'SLA policy status updated.');
    }

    public function deleteSlaPolicy(int $policy): \Illuminate\Http\RedirectResponse
    {
        $policy = SlaPolicy::findOrFail($policy);

        try {
            DB::transaction(function () use ($policy) {
                // Guard against older databases where FK delete behavior may differ.
                SlaTimer::where('sla_policy_id', $policy->id)->delete();
                $policy->delete();
            });
        } catch (\Throwable $e) {
            return redirect()->route('admin.sla')->with('error', 'Unable to delete SLA policy right now.');
        }

        return redirect()->route('admin.sla')->with('success', 'SLA policy deleted.');
    }

    public function updateUser(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,agent,manager,team_lead,end_user,user',
            'is_active' => 'nullable|boolean',
            'teams' => 'array',
            'teams.*' => 'integer|exists:teams,id',
        ]);

        $role = $data['role'] === 'user' ? 'end_user' : $data['role'];

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $role,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $user->teams()->sync($data['teams'] ?? []);

        try {
            $user->syncRoles([$role]);
        } catch (\Throwable) {
            // ignore role-sync failures and keep role column as source of truth
        }

        return redirect()->route('admin.users')->with('success', 'User updated.');
    }

    public function toggleUserStatus(User $user): \Illuminate\Http\RedirectResponse
    {
        $user->is_active = ! ((bool) $user->is_active);
        $user->save();

        return redirect()->route('admin.users')->with('success', $user->is_active ? 'User activated.' : 'User deactivated.');
    }

    public function cancelInvitation(UserInvitation $invitation): \Illuminate\Http\RedirectResponse
    {
        $invitation->delete();

        return redirect()->route('admin.users')->with('success', 'Invitation cancelled.');
    }

    public function sendInvitation(Request $request, SettingService $settings): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'invite_email' => 'required|email|unique:users,email|unique:user_invitations,email',
            'invite_role' => 'required|in:admin,agent,manager,team_lead,end_user,user',
        ]);

        $inviteRole = $data['invite_role'] === 'user' ? 'end_user' : $data['invite_role'];

        $invitation = UserInvitation::create([
            'email' => $data['invite_email'],
            'role' => $inviteRole,
            'token' => Str::random(40),
            'invited_by' => Auth::id(),
            'expires_at' => now()->addDays(7),
        ]);

        $brandName = $settings->get('brand_name', config('app.name', 'ServiceFlow'));

        try {
            Mail::to($invitation->email)->send(new \App\Mail\UserInvitationMail($invitation, $brandName));
        } catch (\Throwable) {
            return redirect()->route('admin.users')->with('success', "Invitation created for {$invitation->email}, but email sending failed. Check mail settings.");
        }

        return redirect()->route('admin.users')->with('success', "Invitation sent to {$invitation->email}.");
    }

    public function provisionTenant(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|regex:/^[a-z0-9-]+$/',
            'admin_name' => 'required|string|max:255',
            'admin_email' => 'required|email',
            'admin_password' => 'required|string|min:8',
        ]);

        try {
            app(TenantProvisioner::class)->provision($data);

            return redirect()->route('admin.tenants')->with('success', "Tenant '{$data['name']}' provisioned successfully.");
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.tenants', ['new' => 1])->withErrors(['subdomain' => $e->getMessage()])->withInput();
        } catch (\Throwable $e) {
            return redirect()->route('admin.tenants', ['new' => 1])->withErrors(['name' => 'Provision failed: ' . $e->getMessage()])->withInput();
        }
    }

    public function suspendTenant(Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        app(TenantProvisioner::class)->suspend($tenant);

        return redirect()->route('admin.tenants')->with('success', 'Tenant suspended.');
    }

    public function activateTenant(Tenant $tenant): \Illuminate\Http\RedirectResponse
    {
        app(TenantProvisioner::class)->activate($tenant);

        return redirect()->route('admin.tenants')->with('success', 'Tenant activated.');
    }

    public function storeUser(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,agent,manager,team_lead,end_user,user',
            'is_active' => 'nullable|boolean',
        ]);

        $role = $data['role'] === 'user' ? 'end_user' : $data['role'];

        $user = User::create([
            'tenant_id' => Auth::user()?->tenant_id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $role,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        $user->syncRoles([$role]);

        return redirect()->route('admin.users')->with('success', "User {$user->name} created successfully.");
    }

    public function storeTeam(Request $request): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'inbound_email' => 'nullable|email|max:255',
            'inbound_email_enabled' => 'nullable|boolean',
        ]);

        Team::create([
            'tenant_id' => Auth::user()?->tenant_id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'inbound_email' => isset($data['inbound_email']) ? strtolower($data['inbound_email']) : null,
            'inbound_email_enabled' => (bool) ($data['inbound_email_enabled'] ?? false),
        ]);

        return redirect()->route('admin.teams')->with('success', 'Team created successfully.');
    }

    public function updateTeam(Request $request, Team $team): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'inbound_email' => 'nullable|email|max:255',
            'inbound_email_enabled' => 'nullable|boolean',
        ]);

        $team->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'inbound_email' => isset($data['inbound_email']) ? strtolower($data['inbound_email']) : null,
            'inbound_email_enabled' => (bool) ($data['inbound_email_enabled'] ?? false),
        ]);

        return redirect()->route('admin.teams')->with('success', 'Team updated successfully.');
    }

    public function updateTeamMembers(Request $request, Team $team): \Illuminate\Http\RedirectResponse
    {
        $data = $request->validate([
            'selectedAgents' => 'array',
            'selectedAgents.*' => 'integer|exists:users,id',
            'team_lead_id' => 'nullable|integer|exists:users,id',
        ]);

        $team->members()->sync($data['selectedAgents'] ?? []);
        $team->update([
            'team_lead_id' => $data['team_lead_id'] ?? null,
        ]);

        return redirect()->route('admin.teams')->with('success', 'Team members updated.');
    }

    public function destroyTeam(Team $team): \Illuminate\Http\RedirectResponse
    {
        try {
            DB::transaction(function () use ($team) {
                // Clear relationships first to avoid FK issues in mixed DB states.
                $team->members()->detach();
                $team->tickets()->update(['team_id' => null]);
                $team->delete();
            });

            return redirect()->route('admin.teams')->with('success', 'Team deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->route('admin.teams')->with('error', 'Unable to delete team right now. Please try again.');
        }
    }

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
