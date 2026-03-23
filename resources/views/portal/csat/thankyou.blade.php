@extends('portal.layout')

@section('title', 'Thank You')

@section('content')
<div class="max-w-lg mx-auto py-16 text-center">
    <div class="text-5xl mb-4">🎉</div>
    <h1 class="text-2xl font-semibold text-gray-800 mb-2">Thank you for your feedback!</h1>
    <p class="text-gray-500">Your response has been recorded. We appreciate you taking the time to rate our support.</p>
    <a href="{{ route('portal.index') }}" class="mt-6 inline-block text-indigo-600 hover:underline">Back to portal</a>
</div>
@endsection
