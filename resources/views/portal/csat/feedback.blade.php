@extends('portal.layout')

@section('title', 'Rate Your Support Experience')

@section('content')
<div class="max-w-lg mx-auto py-12">
    <h1 class="text-2xl font-semibold text-gray-800 mb-2">How did we do?</h1>
    <p class="text-gray-500 mb-6">Please rate your experience for ticket #{{ $survey->ticket->subject ?? $survey->ticket_id }}.</p>

    <form method="POST" action="{{ route('portal.csat.feedback.store', $survey->token) }}" class="space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
            <div class="flex gap-2">
                @for ($i = 1; $i <= 5; $i++)
                    <label class="cursor-pointer">
                        <input type="radio" name="rating" value="{{ $i }}"
                               {{ old('rating', $survey->rating) == $i ? 'checked' : '' }}
                               class="sr-only peer" required>
                        <span class="text-3xl peer-checked:scale-110 transition-transform select-none
                                     {{ old('rating', $survey->rating) >= $i ? 'text-yellow-400' : 'text-gray-300' }}">★</span>
                    </label>
                @endfor
            </div>
            @error('rating') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Comments <span class="text-gray-400">(optional)</span></label>
            <textarea id="comment" name="comment" rows="4"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                      maxlength="1000" placeholder="Tell us more…">{{ old('comment', $survey->comment) }}</textarea>
            @error('comment') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit"
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition">
            Submit Feedback
        </button>
    </form>
</div>
@endsection
