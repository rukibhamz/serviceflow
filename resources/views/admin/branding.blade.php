@extends('layouts.admin')

@section('page-header')
    <div class="page-title">Settings & Branding</div>
    <div class="page-sub">System configuration, branding, and theme</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- General info --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="card-ds">
            <div class="card-hdr"><div class="card-title">General</div></div>
            <div class="card-body space-y-3">
                <div class="form-group">
                    <label class="form-label">App URL</label>
                    <input type="text" class="form-input-ds" value="{{ config('app.url') }}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Environment</label>
                    <input type="text" class="form-input-ds" value="{{ config('app.env') }}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Default Timezone</label>
                    <input type="text" class="form-input-ds" value="{{ config('app.timezone') }}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Mail From</label>
                    <input type="text" class="form-input-ds" value="{{ config('mail.from.address') }}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Queue Driver</label>
                    <input type="text" class="form-input-ds" value="{{ config('queue.default') }}" readonly>
                </div>
                <div class="form-group">
                    <label class="form-label">Cache Driver</label>
                    <input type="text" class="form-input-ds" value="{{ config('cache.default') }}" readonly>
                </div>
            </div>
        </div>

        <div class="card-ds">
            <div class="card-hdr"><div class="card-title">System Status</div></div>
            <div class="card-body space-y-3">
                @php
                    $checks = [
                        ['label' => 'PHP Version',    'value' => PHP_VERSION,                          'ok' => version_compare(PHP_VERSION, '8.2', '>=')],
                        ['label' => 'Storage Writable','value' => is_writable(storage_path()) ? 'Yes' : 'No', 'ok' => is_writable(storage_path())],
                        ['label' => 'App Installed',  'value' => env('APP_INSTALLED') === 'true' ? 'Yes' : 'No', 'ok' => env('APP_INSTALLED') === 'true'],
                        ['label' => 'Debug Mode',     'value' => config('app.debug') ? 'On (disable in production)' : 'Off', 'ok' => !config('app.debug')],
                        ['label' => 'PDO MySQL',      'value' => extension_loaded('pdo_mysql') ? 'Loaded' : 'Missing', 'ok' => extension_loaded('pdo_mysql')],
                        ['label' => 'GD Extension',   'value' => extension_loaded('gd') ? 'Loaded' : 'Missing', 'ok' => extension_loaded('gd')],
                    ];
                @endphp
                @foreach($checks as $check)
                <div class="flex items-center justify-between py-1.5 border-b border-gray-50 last:border-0">
                    <span class="text-sm text-gray-600">{{ $check['label'] }}</span>
                    <span class="flex items-center gap-1.5 text-xs font-medium {{ $check['ok'] ? 'text-green-600' : 'text-red-500' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $check['ok'] ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        {{ $check['value'] }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Branding & Theme --}}
    <livewire:admin.branding-settings />

</div>
@endsection
