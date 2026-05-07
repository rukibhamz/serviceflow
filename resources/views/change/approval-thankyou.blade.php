<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Decision Recorded — {{ app(\App\Services\SettingService::class)->get('brand_name', 'ServiceFlow') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins', 'sans-serif'] } } } }</script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center font-sans p-4">
<div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 text-center">
    <div class="text-5xl mb-4">{{ $decision === 'approved' ? '✅' : '❌' }}</div>
    <h1 class="text-xl font-bold text-gray-900 mb-2">
        Decision Recorded
    </h1>
    <p class="text-gray-500 text-sm">
        You have <strong>{{ $decision }}</strong> the change request
        <strong>{{ $approver->ticket->subject }}</strong>.
        The requester has been notified.
    </p>
    <p class="text-xs text-gray-400 mt-6">You may close this window.</p>
</div>
</body>
</html>
