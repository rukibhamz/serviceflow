<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Already Decided — {{ app(\App\Services\SettingService::class)->get('brand_name', 'ServiceFlow') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { fontFamily: { sans: ['Poppins', 'sans-serif'] } } } }</script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center font-sans p-4">
<div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8 text-center">
    <div class="text-5xl mb-4">ℹ️</div>
    <h1 class="text-xl font-bold text-gray-900 mb-2">Already Decided</h1>
    <p class="text-gray-500 text-sm">
        You already submitted a decision of
        <strong class="{{ $approver->decision === 'approved' ? 'text-green-600' : 'text-red-600' }}">
            {{ $approver->decision }}
        </strong>
        for this change request on {{ $approver->decided_at?->format('d M Y H:i') }}.
    </p>
    <p class="text-xs text-gray-400 mt-6">You may close this window.</p>
</div>
</body>
</html>
