<div>
    {{-- Month navigation --}}
    <div class="flex items-center justify-between mb-4">
        <button wire:click="previousMonth" class="px-3 py-1 rounded border text-sm hover:bg-gray-100">&larr;</button>
        <h2 class="text-lg font-semibold text-gray-700">
            {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
        </h2>
        <button wire:click="nextMonth" class="px-3 py-1 rounded border text-sm hover:bg-gray-100">&rarr;</button>
    </div>

    {{-- Day-of-week headers --}}
    <div class="grid grid-cols-7 text-center text-xs font-medium text-gray-500 mb-1">
        @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
            <div>{{ $dow }}</div>
        @endforeach
    </div>

    {{-- Calendar grid --}}
    <div class="grid grid-cols-7 gap-px bg-gray-200 border border-gray-200 rounded overflow-hidden">
        {{-- Leading empty cells --}}
        @for ($i = 0; $i < $startDow; $i++)
            <div class="bg-gray-50 min-h-[80px]"></div>
        @endfor

        {{-- Day cells --}}
        @for ($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateKey = \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d');
                $dayChanges = $changes[$dateKey] ?? collect();
            @endphp
            <div class="bg-white min-h-[80px] p-1">
                <div class="text-xs font-medium text-gray-400 mb-1">{{ $day }}</div>
                @foreach ($dayChanges as $change)
                    <a href="{{ route('agent.tickets.show', $change->ulid) }}"
                       class="block text-xs truncate rounded px-1 py-0.5 mb-0.5
                              {{ $change->risk_level === 'high' ? 'bg-red-100 text-red-700' : ($change->risk_level === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}"
                       title="{{ $change->subject }}">
                        {{ $change->subject }}
                    </a>
                @endforeach
            </div>
        @endfor
    </div>
</div>
