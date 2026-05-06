@extends('layouts.agent')

@section('page-header')
    <div class="page-title">Settings</div>
    <div class="page-sub">Manage system preferences and branding</div>
@endsection

@section('content')
@php
    $svc        = app(\App\Services\SettingService::class);
    $all        = $svc->all();
    $curName    = $all['brand_name']    ?? 'ServiceFlow';
    $curPreset  = $all['theme_preset']  ?? 'blue';
    $curPrimary = $all['theme_primary'] ?? '#1a4fa0';
    $curAccent  = $all['theme_accent']  ?? '#f97316';
    $curLogo    = $svc->logoUrl();
    $curFavicon = $svc->faviconUrl();
    $presets    = \App\Services\SettingService::presets();
@endphp
<div class="space-y-6">

    {{-- General info (read-only) --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card-ds">
            <div class="card-hdr"><div class="card-title">General</div></div>
            <div class="card-body space-y-3">
                <div class="form-group">
                    <label class="form-label">App URL</label>
                    <input type="text" class="form-input-ds" value="{{ config('app.url') }}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Default Timezone</label>
                    <input type="text" class="form-input-ds" value="{{ config('app.timezone') }}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Mail From</label>
                    <input type="text" class="form-input-ds" value="{{ config('mail.from.address') }}" readonly>
                </div>
            </div>
        </div>
    </div>

    {{-- Branding & Theme --}}
    <div x-data="{ primary: '{{ $curPrimary }}', accent: '{{ $curAccent }}', name: '{{ addslashes($curName) }}' }">
        {{-- Flash --}}
        @if(session('branding_saved'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="mb-4 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Branding saved successfully.
        </div>
        @endif

        <form method="POST"
              action="{{ route('agent.settings.branding.save') }}"
              enctype="multipart/form-data"
              class="space-y-6">
            @csrf

            <div class="card-ds">
                <div class="card-hdr">
                    <div class="card-title">Branding &amp; Theme</div>
                    <span class="text-xs text-gray-400">Admin only</span>
                </div>
                <div class="card-body space-y-5">

                    {{-- Brand Name --}}
                    <div class="form-group">
                        <label class="form-label">Brand / Company Name</label>
                        <input type="text" name="brand_name"
                               value="{{ old('brand_name', $curName) }}"
                               x-on:input="name = $event.target.value"
                               class="form-input-ds max-w-sm"
                               placeholder="Your Company Name" required>
                        @error('brand_name')<span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>@enderror
                    </div>

                    {{-- Logo & Favicon --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                        {{-- Logo --}}
                        <div class="form-group">
                            <label class="form-label">Logo</label>
                            <div class="flex items-start gap-3">
                                <div class="w-24 h-14 rounded-lg border border-gray-200 flex items-center justify-center bg-gray-50 overflow-hidden flex-shrink-0">
                                    @if($curLogo)
                                        <img src="{{ $curLogo }}" class="h-full w-full object-contain p-1">
                                    @else
                                        <span class="text-xs text-gray-300">No logo</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input type="file" name="brand_logo" accept="image/*"
                                           class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer w-full">
                                    <p class="text-xs text-gray-400 mt-1">PNG, SVG, JPEG · Max 2 MB</p>
                                    @if($curLogo)
                                        <label class="flex items-center gap-1.5 mt-1.5 cursor-pointer text-xs text-red-400 hover:text-red-600">
                                            <input type="checkbox" name="remove_logo" value="1" class="rounded">
                                            Remove logo
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Favicon --}}
                        <div class="form-group">
                            <label class="form-label">Favicon</label>
                            <div class="flex items-start gap-3">
                                <div class="w-14 h-14 rounded-lg border border-gray-200 flex items-center justify-center bg-gray-50 overflow-hidden flex-shrink-0">
                                    @if($all['brand_favicon'] ?? null)
                                        <img src="{{ $curFavicon }}" alt="Favicon" class="w-8 h-8 object-contain">
                                    @else
                                        <span class="text-xs text-gray-300">None</span>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <input type="file" name="brand_favicon" accept=".ico,.png,.jpg,.jpeg,.svg"
                                           class="text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer w-full">
                                    <p class="text-xs text-gray-400 mt-1">ICO, PNG, SVG · Max 512 KB</p>
                                    @if($all['brand_favicon'] ?? null)
                                        <label class="flex items-center gap-1.5 mt-1.5 cursor-pointer text-xs text-red-400 hover:text-red-600">
                                            <input type="checkbox" name="remove_favicon" value="1" class="rounded">
                                            Remove favicon
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Preset swatches --}}
                    <div class="form-group">
                        <label class="form-label">Theme Preset</label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-1">
                            @foreach($presets as $key => $preset)
                            <label class="cursor-pointer">
                                <input type="radio" name="theme_preset" value="{{ $key }}"
                                       {{ old('theme_preset', $curPreset) === $key ? 'checked' : '' }}
                                       class="sr-only peer"
                                       @if($key !== 'custom')
                                       x-on:change="
                                           primary = '{{ $preset['primary'] }}';
                                           accent  = '{{ $preset['accent'] }}';
                                           document.getElementById('inp_primary').value = '{{ $preset['primary'] }}';
                                           document.getElementById('inp_accent').value  = '{{ $preset['accent'] }}';
                                       "
                                       @endif>
                                <div class="relative flex flex-col items-center gap-2 p-3 rounded-xl border-2 border-gray-100 peer-checked:border-blue-500 peer-checked:bg-blue-50/40 bg-white hover:bg-gray-50 transition select-none">
                                    @if($key !== 'custom')
                                        <div class="flex gap-1.5">
                                            <div class="w-6 h-6 rounded-full shadow border border-black/10" style="background:{{ $preset['primary'] }}"></div>
                                            <div class="w-6 h-6 rounded-full shadow border border-black/10" style="background:{{ $preset['accent'] }}"></div>
                                        </div>
                                    @else
                                        <div class="w-12 h-6 rounded-full bg-gradient-to-r from-purple-400 via-pink-400 to-orange-400 shadow"></div>
                                    @endif
                                    <span class="text-xs font-medium text-gray-700 text-center leading-tight">{{ $preset['label'] }}</span>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Color pickers --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="form-group">
                            <label class="form-label">Primary Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="inp_primary" name="theme_primary"
                                       value="{{ old('theme_primary', $curPrimary) }}"
                                       x-on:input="primary = $event.target.value; document.getElementById('inp_primary_txt').value = $event.target.value"
                                       class="w-10 h-9 rounded border border-gray-200 cursor-pointer p-0.5 flex-shrink-0">
                                <input type="text" id="inp_primary_txt"
                                       value="{{ old('theme_primary', $curPrimary) }}"
                                       x-on:input="if(/^#[0-9a-fA-F]{6}$/.test($event.target.value)){ primary=$event.target.value; document.getElementById('inp_primary').value=$event.target.value; }"
                                       class="form-input-ds font-mono text-xs" placeholder="#1a4fa0" maxlength="7">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Accent / Secondary Color</label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="inp_accent" name="theme_accent"
                                       value="{{ old('theme_accent', $curAccent) }}"
                                       x-on:input="accent = $event.target.value; document.getElementById('inp_accent_txt').value = $event.target.value"
                                       class="w-10 h-9 rounded border border-gray-200 cursor-pointer p-0.5 flex-shrink-0">
                                <input type="text" id="inp_accent_txt"
                                       value="{{ old('theme_accent', $curAccent) }}"
                                       x-on:input="if(/^#[0-9a-fA-F]{6}$/.test($event.target.value)){ accent=$event.target.value; document.getElementById('inp_accent').value=$event.target.value; }"
                                       class="form-input-ds font-mono text-xs" placeholder="#f97316" maxlength="7">
                            </div>
                        </div>
                    </div>

                    {{-- Live preview --}}
                    <div>
                        <p class="text-xs text-gray-400 mb-1.5">Live preview</p>
                        <div class="rounded-xl border border-gray-100 overflow-hidden shadow-sm">
                            <div class="h-10 flex items-center gap-3 px-4 text-white text-xs font-medium"
                                 :style="'background:' + primary">
                                <div class="w-2 h-2 rounded-full" :style="'background:' + accent"></div>
                                <span x-text="name || 'Your Company'"></span>
                                <div class="ml-auto w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
                                     :style="'background:' + accent">AG</div>
                            </div>
                            <div class="flex" style="height:52px;">
                                <div class="w-36 flex flex-col justify-center gap-1.5 px-3 py-2"
                                     :style="'background:' + primary + 'dd'">
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

            <div class="flex items-center gap-3">
                <button type="submit" class="btn-ds primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Branding
                </button>
            </div>

        </form>
    </div>

</div>

{{-- After save: patch the live theme vars without a full reload --}}
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
