@extends('installer.layout')
@php $currentStep = 3; @endphp

@section('content')
<h2 class="text-xl font-semibold text-gray-800 mb-4">Company &amp; Branding</h2>
<p class="text-gray-500 mb-6 text-sm">Set how your help desk appears to agents and customers. You can change these later in Admin → Settings.</p>

@if ($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('installer.branding.store') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Company / Brand Name</label>
        <input type="text" name="brand_name" value="{{ $curName }}" required maxlength="80"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
               placeholder="e.g. Acme Support">
        <p class="text-xs text-gray-400 mt-1">Shown on the login page, portal, and email notifications.</p>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Logo <span class="text-gray-400 font-normal">(optional)</span></label>
            <input type="file" name="brand_logo" accept="image/*"
                   class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Favicon <span class="text-gray-400 font-normal">(optional)</span></label>
            <input type="file" name="brand_favicon" accept=".ico,.png,.jpg,.jpeg,.svg"
                   class="w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Theme</label>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
            @foreach ($presets as $key => $preset)
                <label class="cursor-pointer">
                    <input type="radio" name="theme_preset" value="{{ $key }}" class="sr-only peer preset-radio"
                           data-primary="{{ $preset['primary'] ?? '' }}"
                           data-accent="{{ $preset['accent'] ?? '' }}"
                           {{ $curPreset === $key ? 'checked' : '' }}>
                    <div class="flex flex-col items-center gap-1.5 p-2.5 rounded-lg border-2 border-gray-100 peer-checked:border-blue-500 peer-checked:bg-blue-50/40 hover:bg-gray-50 transition">
                        @if ($key !== 'custom' && $preset['primary'])
                            <div class="flex gap-1">
                                <div class="w-5 h-5 rounded-full border border-black/10 shadow-sm" style="background:{{ $preset['primary'] }}"></div>
                                <div class="w-5 h-5 rounded-full border border-black/10 shadow-sm" style="background:{{ $preset['accent'] }}"></div>
                            </div>
                        @else
                            <div class="w-10 h-5 rounded-full bg-gradient-to-r from-purple-400 via-pink-400 to-orange-400"></div>
                        @endif
                        <span class="text-xs font-medium text-gray-600 text-center leading-tight">{{ $preset['label'] }}</span>
                    </div>
                </label>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Primary Color</label>
            <div class="flex items-center gap-2">
                <input type="color" id="theme_primary" name="theme_primary" value="{{ $curPrimary }}"
                       class="w-10 h-9 rounded border border-gray-200 cursor-pointer p-0.5">
                <input type="text" id="theme_primary_txt" value="{{ $curPrimary }}" maxlength="7"
                       class="flex-1 border border-gray-300 rounded-lg px-2 py-1.5 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Accent Color</label>
            <div class="flex items-center gap-2">
                <input type="color" id="theme_accent" name="theme_accent" value="{{ $curAccent }}"
                       class="w-10 h-9 rounded border border-gray-200 cursor-pointer p-0.5">
                <input type="text" id="theme_accent_txt" value="{{ $curAccent }}" maxlength="7"
                       class="flex-1 border border-gray-300 rounded-lg px-2 py-1.5 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>
    </div>

    <div class="flex justify-between pt-2">
        <a href="{{ route('installer.database') }}" class="text-sm text-gray-500 hover:underline self-center">← Back</a>
        <button type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
            Continue →
        </button>
    </div>
</form>

<script>
(function () {
    function bindColorPair(colorId, textId) {
        const color = document.getElementById(colorId);
        const text = document.getElementById(textId);
        color.addEventListener('input', () => { text.value = color.value; });
        text.addEventListener('input', () => {
            if (/^#[0-9a-fA-F]{6}$/.test(text.value)) color.value = text.value;
        });
    }
    bindColorPair('theme_primary', 'theme_primary_txt');
    bindColorPair('theme_accent', 'theme_accent_txt');

    document.querySelectorAll('.preset-radio').forEach((radio) => {
        radio.addEventListener('change', () => {
            const primary = radio.dataset.primary;
            const accent = radio.dataset.accent;
            if (!primary || !accent) return;
            document.getElementById('theme_primary').value = primary;
            document.getElementById('theme_primary_txt').value = primary;
            document.getElementById('theme_accent').value = accent;
            document.getElementById('theme_accent_txt').value = accent;
        });
    });
})();
</script>
@endsection
