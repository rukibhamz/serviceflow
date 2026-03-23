<?php

namespace App\Livewire\Change;

use App\Models\Ticket;
use Livewire\Component;

class ChangeCalendar extends Component
{
    public int $year;
    public int $month;

    public function mount(): void
    {
        $this->year  = (int) now()->format('Y');
        $this->month = (int) now()->format('n');
    }

    public function previousMonth(): void
    {
        $date = \Carbon\Carbon::create($this->year, $this->month, 1)->subMonth();
        $this->year  = $date->year;
        $this->month = $date->month;
    }

    public function nextMonth(): void
    {
        $date = \Carbon\Carbon::create($this->year, $this->month, 1)->addMonth();
        $this->year  = $date->year;
        $this->month = $date->month;
    }

    public function render()
    {
        $start = \Carbon\Carbon::create($this->year, $this->month, 1)->startOfMonth();
        $end   = $start->copy()->endOfMonth();

        $changes = Ticket::where('type', 'change')
            ->whereNotNull('scheduled_at')
            ->whereBetween('scheduled_at', [$start, $end])
            ->with('assignee')
            ->orderBy('scheduled_at')
            ->get()
            ->groupBy(fn ($t) => \Carbon\Carbon::parse($t->scheduled_at)->format('Y-m-d'));

        return view('livewire.change.change-calendar', [
            'changes'    => $changes,
            'calStart'   => $start,
            'daysInMonth' => $start->daysInMonth,
            'startDow'   => $start->dayOfWeek, // 0=Sun
        ]);
    }
}
