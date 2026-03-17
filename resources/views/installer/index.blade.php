@extends('installer.layout')
@php $currentStep = 1; @endphp

@section('content')
<h2 class="text-xl font-semibold text-gray-800 mb-4">Environment Check</h2>
<p class="text-gray-500 mb-6 text-sm">Verifying your server meets the requirements to run ServiceFlow.</p>

<table class="w-full text-sm">
    <thead>
        <tr class="text-left text-gray-500 border-b">
            <th class="pb-2">Check</th>
            <th class="pb-2">Status</th>
            <th class="pb-2">Message</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($results as $result)
        <tr class="border-b last:border-0">
            <td class="py-2 font-medium text-gray-700">{{ $result['name'] }}</td>
            <td class="py-2">
                @if ($result['status'] === 'pass')
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-700">Pass</span>
                @elseif ($result['status'] === 'warn')
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-yellow-100 text-yellow-700">Warn</span>
                @else
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-700">Fail</span>
                @endif
            </td>
            <td class="py-2 text-gray-500">{{ $result['message'] }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="mt-6 flex justify-end">
    @if ($allPassed)
        <a href="{{ route('installer.database') }}"
           class="bg-blue-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700">
            Continue →
        </a>
    @else
        <p class="text-red-600 text-sm mr-4 self-center">Fix the failing checks before continuing.</p>
        <a href="{{ route('installer.index') }}"
           class="bg-gray-200 text-gray-700 px-5 py-2 rounded-lg text-sm font-semibold hover:bg-gray-300">
            Re-check
        </a>
    @endif
</div>
@endsection
