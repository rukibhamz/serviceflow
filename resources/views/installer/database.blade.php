@extends('installer.layout')
@php $currentStep = 2; @endphp

@section('content')
<h2 class="text-xl font-semibold text-gray-800 mb-4">Database Configuration</h2>
<p class="text-gray-500 mb-6 text-sm">Enter your database credentials. ServiceFlow will run migrations and seed initial data.</p>

@if ($errors->any())
    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded text-red-700 text-sm">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ route('installer.database.store') }}" class="space-y-4">
    @csrf

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Connection</label>
        <select name="connection" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="mysql" {{ old('connection', 'mysql') === 'mysql' ? 'selected' : '' }}>MySQL</option>
            <option value="pgsql" {{ old('connection') === 'pgsql' ? 'selected' : '' }}>PostgreSQL</option>
        </select>
    </div>

    <div class="grid grid-cols-3 gap-3">
        <div class="col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Host</label>
            <input type="text" name="host" value="{{ old('host', '127.0.0.1') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
            <input type="text" name="port" value="{{ old('port', '3306') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Database Name</label>
        <input type="text" name="database" value="{{ old('database') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input type="text" name="username" value="{{ old('username') }}"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input type="password" name="password"
               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
    </div>

    <div class="flex justify-between pt-2">
        <a href="{{ route('installer.index') }}" class="text-sm text-gray-500 hover:underline self-center">← Back</a>
        <div class="flex items-center gap-3">
            <button type="button" id="test-btn"
                    class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50">
                Test Connection
            </button>
            <button type="submit"
                    class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
                Install →
            </button>
        </div>
    </div>

    <div id="test-result" class="hidden mt-3 p-3 rounded-lg text-sm"></div>
</form>

<script>
document.getElementById('test-btn').addEventListener('click', async function () {
    const btn = this;
    const result = document.getElementById('test-result');
    const form = btn.closest('form');

    btn.disabled = true;
    btn.textContent = 'Testing…';
    result.className = 'hidden mt-3 p-3 rounded-lg text-sm';

    const data = new FormData(form);

    try {
        const res = await fetch('{{ route('installer.database.test') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': data.get('_token'), 'Accept': 'application/json' },
            body: data,
        });
        const json = await res.json();

        result.textContent = json.message;
        result.className = res.ok
            ? 'mt-3 p-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-700'
            : 'mt-3 p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700';
    } catch (e) {
        result.textContent = 'Unexpected error. Please try again.';
        result.className = 'mt-3 p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-700';
    } finally {
        btn.disabled = false;
        btn.textContent = 'Test Connection';
    }
});
</script>
@endsection
