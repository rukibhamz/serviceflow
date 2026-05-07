<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CAB Approval — {{ app(\App\Services\SettingService::class)->get('brand_name', 'ServiceFlow') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ app(\App\Services\SettingService::class)->faviconUrl() }}" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins', 'sans-serif'] } } } }</script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center font-sans p-4">

<div class="w-full max-w-lg bg-white rounded-2xl shadow-lg p-8">
    <div class="mb-6">
        <p class="text-xs font-semibold text-indigo-600 uppercase tracking-wide mb-1">CAB Approval Request</p>
        <h1 class="text-xl font-bold text-gray-900">{{ $approver->ticket->subject }}</h1>
        <p class="text-sm text-gray-500 mt-1">Requested by {{ $approver->ticket->requester?->name ?? 'Unknown' }}</p>
    </div>

    {{-- Change details --}}
    <div class="bg-gray-50 rounded-xl p-4 mb-6 space-y-2 text-sm">
        <div class="flex justify-between">
            <span class="text-gray-500">Change Type</span>
            <span class="font-medium text-gray-800">{{ ucfirst($approver->ticket->change_type ?? 'Normal') }}</span>
        </div>
        <div class="flex justify-between">
            <span class="text-gray-500">Risk Level</span>
            <span class="font-medium {{ match($approver->ticket->risk_level) { 'high' => 'text-red-600', 'medium' => 'text-yellow-600', default => 'text-green-600' } }}">
                {{ ucfirst($approver->ticket->risk_level ?? 'Low') }}
            </span>
        </div>
        @if($approver->ticket->scheduled_at)
        <div class="flex justify-between">
            <span class="text-gray-500">Scheduled</span>
            <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($approver->ticket->scheduled_at)->format('d M Y H:i') }}</span>
        </div>
        @endif
        @if($approver->ticket->description)
        <div class="pt-2 border-t border-gray-200">
            <p class="text-gray-500 mb-1">Description</p>
            <p class="text-gray-800 whitespace-pre-wrap text-xs">{{ $approver->ticket->description }}</p>
        </div>
        @endif
    </div>

    <form method="POST" action="{{ route('change.approval.submit', $approver->token) }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Comment <span class="text-gray-400 font-normal">(optional)</span></label>
            <textarea name="comment" rows="3"
                      class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      placeholder="Add a comment to your decision…">{{ old('comment') }}</textarea>
        </div>

        <div class="grid grid-cols-2 gap-3 pt-2">
            <button type="submit" name="decision" value="approved"
                    class="w-full py-3 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition-colors">
                ✅ Approve
            </button>
            <button type="submit" name="decision" value="rejected"
                    class="w-full py-3 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition-colors">
                ❌ Reject
            </button>
        </div>
    </form>
</div>

</body>
</html>
