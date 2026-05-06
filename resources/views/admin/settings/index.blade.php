@extends('layouts.admin')

@section('page-header')
    <div class="page-title">System Settings</div>
    <div class="page-sub">Manage branding, authentication and system configuration</div>
@endsection

@section('content')
@php
    $svc = app(\App\Services\SettingService::class);
    $all = $svc->all();
    $curName = $all['brand_name'] ?? 'ServiceFlow';
    $curPreset = $all['theme_preset'] ?? 'blue';
    $curPrimary = $all['theme_primary'] ?? '#1a4fa0';
    $curAccent = $all['theme_accent'] ?? '#f97316';
    $curLogo = $svc->logoUrl();
    $curFavicon = $svc->faviconUrl();
    $presets = \App\Services\SettingService::presets();
    $ssoEnabled = (bool) ($all['sso_enabled'] ?? true);
    $providers = [
        'google' => ['label' => 'Google', 'config_key' => 'google', 'has_tenant' => false],
        'microsoft' => ['label' => 'Microsoft', 'config_key' => 'azure', 'has_tenant' => true],
        'github' => ['label' => 'GitHub', 'config_key' => 'github', 'has_tenant' => false],
        'atlassian' => ['label' => 'Atlassian', 'config_key' => 'atlassian', 'has_tenant' => false],
        'slack' => ['label' => 'Slack', 'config_key' => 'slack-openid', 'has_tenant' => false],
    ];
@endphp

<div class="space-y-6">
    @if(session('mail_saved'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Mail settings saved successfully.
        </div>
    @endif
    @if(session('mail_test_result'))
        <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
            {{ session('mail_test_result') }}
        </div>
    @endif
    @if(session('sso_saved'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            SSO settings saved successfully.
        </div>
    @endif

    @if(session('branding_saved'))
        <div class="flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            Branding settings saved successfully.
        </div>
    @endif

    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">Mail Settings (SMTP + Inbox)</div></div>
        <div class="card-body space-y-4">
            <form method="POST" action="{{ route('admin.settings.mail.save') }}" class="space-y-4">
                @csrf
                @php
                    $mailMailer = old('mail_mailer', $all['mail_mailer'] ?? config('mail.default'));
                    $mailHost = old('mail_host', $all['mail_host'] ?? config('mail.mailers.smtp.host'));
                    $mailPort = old('mail_port', $all['mail_port'] ?? config('mail.mailers.smtp.port'));
                    $mailUser = old('mail_username', $all['mail_username'] ?? config('mail.mailers.smtp.username'));
                    $mailPass = old('mail_password', $all['mail_password'] ?? config('mail.mailers.smtp.password'));
                    $mailEnc = old('mail_encryption', $all['mail_encryption'] ?? config('mail.mailers.smtp.scheme'));
                    $mailFromAddress = old('mail_from_address', $all['mail_from_address'] ?? config('mail.from.address'));
                    $mailFromName = old('mail_from_name', $all['mail_from_name'] ?? config('mail.from.name'));
                    $inboundEnabled = old('mail_inbound_enabled', (bool) ($all['mail_inbound_enabled'] ?? false));
                    $inboundProtocol = old('mail_inbound_protocol', $all['mail_inbound_protocol'] ?? 'imap');
                    $inboundHost = old('mail_inbound_host', $all['mail_inbound_host'] ?? '');
                    $inboundPort = old('mail_inbound_port', $all['mail_inbound_port'] ?? 993);
                    $inboundUser = old('mail_inbound_username', $all['mail_inbound_username'] ?? '');
                    $inboundPass = old('mail_inbound_password', $all['mail_inbound_password'] ?? '');
                    $inboundEncryption = old('mail_inbound_encryption', $all['mail_inbound_encryption'] ?? 'tls');
                    $inboundFolder = old('mail_inbound_folder', $all['mail_inbound_folder'] ?? 'INBOX');
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div class="form-group">
                        <label class="form-label">Mailer</label>
                        <select name="mail_mailer" class="form-input-ds">
                            <option value="smtp" {{ $mailMailer === 'smtp' ? 'selected' : '' }}>SMTP</option>
                            <option value="log" {{ $mailMailer === 'log' ? 'selected' : '' }}>Log (dev)</option>
                            <option value="array" {{ $mailMailer === 'array' ? 'selected' : '' }}>Array (test)</option>
                        </select>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                    <div class="text-sm font-semibold text-gray-800">Outgoing SMTP</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="form-group"><label class="form-label">SMTP Host</label><input type="text" name="mail_host" value="{{ $mailHost }}" class="form-input-ds" placeholder="smtp.gmail.com"></div>
                        <div class="form-group"><label class="form-label">SMTP Port</label><input type="number" name="mail_port" value="{{ $mailPort }}" class="form-input-ds" placeholder="587"></div>
                        <div class="form-group"><label class="form-label">Username</label><input type="text" name="mail_username" value="{{ $mailUser }}" class="form-input-ds"></div>
                        <div class="form-group"><label class="form-label">Password / App Password</label><input type="text" name="mail_password" value="{{ $mailPass }}" class="form-input-ds"></div>
                        <div class="form-group"><label class="form-label">Encryption</label>
                            <select name="mail_encryption" class="form-input-ds">
                                <option value="">None</option>
                                <option value="tls" {{ $mailEnc === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ $mailEnc === 'ssl' ? 'selected' : '' }}>SSL</option>
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">From Address</label><input type="email" name="mail_from_address" value="{{ $mailFromAddress }}" class="form-input-ds"></div>
                        <div class="form-group md:col-span-2"><label class="form-label">From Name</label><input type="text" name="mail_from_name" value="{{ $mailFromName }}" class="form-input-ds"></div>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-semibold text-gray-800">Inbound Mail (create/update tickets)</div>
                        <label class="text-xs flex items-center gap-2 text-gray-600">
                            <input type="checkbox" name="mail_inbound_enabled" value="1" class="rounded border-gray-300" {{ $inboundEnabled ? 'checked' : '' }}>
                            Enabled
                        </label>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="form-group"><label class="form-label">Protocol</label>
                            <select name="mail_inbound_protocol" class="form-input-ds">
                                <option value="imap" {{ $inboundProtocol === 'imap' ? 'selected' : '' }}>IMAP (poll inbox)</option>
                                <option value="piped" {{ $inboundProtocol === 'piped' ? 'selected' : '' }}>Piped email (stdin to artisan email:ingest)</option>
                            </select>
                        </div>
                        <div class="form-group"><label class="form-label">Inbox Folder</label><input type="text" name="mail_inbound_folder" value="{{ $inboundFolder }}" class="form-input-ds" placeholder="INBOX"></div>
                        <div class="form-group"><label class="form-label">IMAP Host</label><input type="text" name="mail_inbound_host" value="{{ $inboundHost }}" class="form-input-ds" placeholder="imap.gmail.com"></div>
                        <div class="form-group"><label class="form-label">IMAP Port</label><input type="number" name="mail_inbound_port" value="{{ $inboundPort }}" class="form-input-ds" placeholder="993"></div>
                        <div class="form-group"><label class="form-label">IMAP Username</label><input type="text" name="mail_inbound_username" value="{{ $inboundUser }}" class="form-input-ds"></div>
                        <div class="form-group"><label class="form-label">IMAP Password</label><input type="text" name="mail_inbound_password" value="{{ $inboundPass }}" class="form-input-ds"></div>
                        <div class="form-group"><label class="form-label">IMAP Encryption</label>
                            <select name="mail_inbound_encryption" class="form-input-ds">
                                <option value="tls" {{ $inboundEncryption === 'tls' ? 'selected' : '' }}>TLS</option>
                                <option value="ssl" {{ $inboundEncryption === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="none" {{ $inboundEncryption === 'none' ? 'selected' : '' }}>None</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="btn-ds primary">Save Mail Settings</button>
                </div>
            </form>

            <form method="POST" action="{{ route('admin.settings.mail.test') }}" class="border-t border-gray-100 pt-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                    <div class="form-group md:col-span-2">
                        <label class="form-label">Send Test Email To</label>
                        <input type="email" name="test_email" class="form-input-ds" placeholder="you@company.com" required>
                        @error('mail_test')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="btn-ds">Run Mail Test</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card-ds">
        <div class="card-hdr"><div class="card-title">Single Sign-On (SSO)</div></div>
        <div class="card-body space-y-4">
            <form method="POST" action="{{ route('admin.settings.sso.save') }}" class="space-y-4">
                @csrf
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="sso_enabled" value="1" class="rounded border-gray-300" {{ old('sso_enabled', $ssoEnabled) ? 'checked' : '' }}>
                    Enable SSO on login page
                </label>

                <div class="space-y-4">
                    @foreach($providers as $key => $provider)
                        @php
                            $flag = 'sso_' . $key . '_enabled';
                            $isEnabled = (bool) ($all[$flag] ?? true);
                            $clientIdKey = 'sso_' . $key . '_client_id';
                            $clientSecretKey = 'sso_' . $key . '_client_secret';
                            $redirectKey = 'sso_' . $key . '_redirect';
                            $tenantKey = 'sso_' . $key . '_tenant';
                            $clientIdVal = old($clientIdKey, $all[$clientIdKey] ?? config('services.' . $provider['config_key'] . '.client_id'));
                            $clientSecretVal = old($clientSecretKey, $all[$clientSecretKey] ?? config('services.' . $provider['config_key'] . '.client_secret'));
                            $redirectVal = old($redirectKey, $all[$redirectKey] ?? config('services.' . $provider['config_key'] . '.redirect'));
                            $tenantVal = old($tenantKey, $all[$tenantKey] ?? config('services.' . $provider['config_key'] . '.tenant'));
                            $configured = !empty($clientIdVal) && !empty($clientSecretVal);
                        @endphp
                        <div class="p-4 border border-gray-200 rounded-lg space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-800">
                                    {{ $provider['label'] }}
                                    <span class="ml-2 text-xs {{ $configured ? 'text-green-600' : 'text-amber-600' }}">
                                        {{ $configured ? 'configured' : 'missing keys' }}
                                    </span>
                                </span>
                                <label class="text-xs flex items-center gap-2 text-gray-600">
                                    <input type="checkbox" name="{{ $flag }}" value="1" class="rounded border-gray-300" {{ old($flag, $isEnabled) ? 'checked' : '' }}>
                                    Enabled
                                </label>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div class="form-group">
                                    <label class="form-label">Client ID</label>
                                    <input type="text" name="{{ $clientIdKey }}" value="{{ $clientIdVal }}" class="form-input-ds" placeholder="{{ strtoupper($key) }} client ID">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Client Secret</label>
                                    <input type="text" name="{{ $clientSecretKey }}" value="{{ $clientSecretVal }}" class="form-input-ds" placeholder="{{ strtoupper($key) }} client secret">
                                </div>
                                <div class="form-group md:col-span-2">
                                    <label class="form-label">Redirect URL</label>
                                    <input type="url" name="{{ $redirectKey }}" value="{{ $redirectVal }}" class="form-input-ds" placeholder="{{ url('/auth/'.$key.'/callback') }}">
                                </div>
                                @if($provider['has_tenant'])
                                    <div class="form-group md:col-span-2">
                                        <label class="form-label">Tenant</label>
                                        <input type="text" name="{{ $tenantKey }}" value="{{ $tenantVal }}" class="form-input-ds" placeholder="common or your tenant ID">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-xs text-gray-500">
                    Credentials are saved in application settings and applied at runtime to the Socialite provider configuration.
                </p>

                <div class="flex justify-end">
                    <button type="submit" class="btn-ds primary">Save SSO Settings</button>
                </div>
            </form>
        </div>
    </div>

    <div x-data="{ primary: '{{ $curPrimary }}', accent: '{{ $curAccent }}', name: '{{ addslashes($curName) }}' }">
        <form method="POST" action="{{ route('admin.settings.branding.save') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div class="card-ds">
                <div class="card-hdr"><div class="card-title">Branding &amp; Theme</div></div>
                <div class="card-body space-y-5">
                    <div class="form-group">
                        <label class="form-label">Brand / Company Name</label>
                        <input type="text" name="brand_name" value="{{ old('brand_name', $curName) }}" x-on:input="name = $event.target.value" class="form-input-ds max-w-sm" required>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Logo</label>
                            <div class="flex items-start gap-3">
                                <div class="w-24 h-14 rounded-lg border border-gray-200 flex items-center justify-center bg-gray-50 overflow-hidden flex-shrink-0">
                                    @if($curLogo)<img src="{{ $curLogo }}" class="h-full w-full object-contain p-1">@else<span class="text-xs text-gray-300">No logo</span>@endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input type="file" name="brand_logo" accept="image/*" class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer w-full">
                                    @if($curLogo)<label class="flex items-center gap-1.5 mt-1.5 cursor-pointer text-xs text-red-400 hover:text-red-600"><input type="checkbox" name="remove_logo" value="1" class="rounded">Remove logo</label>@endif
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Favicon</label>
                            <div class="flex items-start gap-3">
                                <div class="w-14 h-14 rounded-lg border border-gray-200 flex items-center justify-center bg-gray-50 overflow-hidden flex-shrink-0">
                                    @if($all['brand_favicon'] ?? null)<img src="{{ $curFavicon }}" class="w-8 h-8 object-contain">@else<span class="text-xs text-gray-300">None</span>@endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input type="file" name="brand_favicon" accept=".ico,.png,.jpg,.jpeg,.svg" class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer w-full">
                                    @if($all['brand_favicon'] ?? null)<label class="flex items-center gap-1.5 mt-1.5 cursor-pointer text-xs text-red-400 hover:text-red-600"><input type="checkbox" name="remove_favicon" value="1" class="rounded">Remove favicon</label>@endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Theme Preset</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-1">
                            @foreach($presets as $key => $preset)
                                <label class="cursor-pointer">
                                    <input type="radio" name="theme_preset" value="{{ $key }}" {{ old('theme_preset', $curPreset) === $key ? 'checked' : '' }} class="sr-only peer"
                                           @if($key !== 'custom')
                                           x-on:change="
                                               primary = '{{ $preset['primary'] }}';
                                               accent  = '{{ $preset['accent'] }}';
                                               document.getElementById('inp_primary').value = '{{ $preset['primary'] }}';
                                               document.getElementById('inp_accent').value  = '{{ $preset['accent'] }}';
                                               document.getElementById('inp_primary_txt').value = '{{ $preset['primary'] }}';
                                               document.getElementById('inp_accent_txt').value  = '{{ $preset['accent'] }}';
                                           "
                                           @endif>
                                    <div class="relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 border-gray-100 peer-checked:border-blue-500 peer-checked:bg-blue-50/40 bg-white hover:bg-gray-50 transition select-none">
                                        @if($key !== 'custom')
                                            <div class="flex gap-1.5"><div class="w-6 h-6 rounded-full shadow border border-black/10" style="background:{{ $preset['primary'] }}"></div><div class="w-6 h-6 rounded-full shadow border border-black/10" style="background:{{ $preset['accent'] }}"></div></div>
                                        @else
                                            <div class="w-12 h-6 rounded-full bg-gradient-to-r from-purple-400 via-pink-400 to-orange-400 shadow"></div>
                                        @endif
                                        <span class="text-xs font-medium text-gray-700 text-center leading-tight">{{ $preset['label'] }}</span>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Primary Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="inp_primary" name="theme_primary" value="{{ old('theme_primary', $curPrimary) }}"
                                       x-on:input="primary = $event.target.value; document.getElementById('inp_primary_txt').value = $event.target.value"
                                       class="w-10 h-9 rounded border border-gray-200 cursor-pointer p-0.5 flex-shrink-0">
                                <input type="text" id="inp_primary_txt" value="{{ old('theme_primary', $curPrimary) }}"
                                       x-on:input="if(/^#[0-9a-fA-F]{6}$/.test($event.target.value)){ primary=$event.target.value; document.getElementById('inp_primary').value=$event.target.value; }"
                                       class="form-input-ds font-mono text-xs" maxlength="7">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Accent Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="inp_accent" name="theme_accent" value="{{ old('theme_accent', $curAccent) }}"
                                       x-on:input="accent = $event.target.value; document.getElementById('inp_accent_txt').value = $event.target.value"
                                       class="w-10 h-9 rounded border border-gray-200 cursor-pointer p-0.5 flex-shrink-0">
                                <input type="text" id="inp_accent_txt" value="{{ old('theme_accent', $curAccent) }}"
                                       x-on:input="if(/^#[0-9a-fA-F]{6}$/.test($event.target.value)){ accent=$event.target.value; document.getElementById('inp_accent').value=$event.target.value; }"
                                       class="form-input-ds font-mono text-xs" maxlength="7">
                            </div>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1.5">Live preview</p>
                        <div class="rounded-xl border border-gray-100 overflow-hidden shadow-sm">
                            <div class="h-10 flex items-center gap-3 px-4 text-white text-xs font-medium" :style="'background:' + primary">
                                <div class="w-2 h-2 rounded-full" :style="'background:' + accent"></div>
                                <span x-text="name || 'Your Company'"></span>
                                <div class="ml-auto w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold" :style="'background:' + accent">AD</div>
                            </div>
                            <div class="flex" style="height:52px;">
                                <div class="w-36 flex flex-col justify-center gap-1.5 px-3 py-2" :style="'background:' + primary + 'dd'">
                                    <div class="h-2 rounded-full" :style="'background:' + accent + '; width:70%'"></div>
                                    <div class="h-2 rounded-full bg-white w-4/5" style="opacity:0.4"></div>
                                    <div class="h-2 rounded-full bg-white w-3/5" style="opacity:0.3"></div>
                                </div>
                                <div class="flex-1 bg-gray-50 flex items-center px-4">
                                    <div class="space-y-1.5 w-full">
                                        <div class="h-2 rounded bg-gray-200 w-3/4"></div>
                                        <div class="h-2 rounded bg-gray-200 w-1/2"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="h-1" :style="'background:' + accent"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end"><button type="submit" class="btn-ds primary">Save Branding</button></div>
        </form>
    </div>
</div>
@if(session('branding_saved'))
<script>
    (function(){
        var primary = '{{ $curPrimary }}';
        var accent  = '{{ $curAccent }}';
        var s = document.getElementById('theme-vars');
        if(s){ s.textContent = ':root{--brand:'+primary+';--brand-lt:'+primary+'cc;--brand-dim:'+primary+'33;--accent:'+accent+';}'; }
        var topnav = document.querySelector('.topnav');
        if(topnav){ topnav.style.background = primary; }
        var sidebar = document.querySelector('.sidebar-shell');
        if(sidebar){ sidebar.style.background = primary; }
        var dot = document.querySelector('.logo-dot');
        if(dot){ dot.style.background = accent; }
        var avatar = document.querySelector('.nav-avatar');
        if(avatar){ avatar.style.background = accent; }
    })();
</script>
@endif
@endsection
