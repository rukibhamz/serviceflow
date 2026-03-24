<?php

namespace App\Livewire\Portal;

use App\Models\Ticket;
use Livewire\Component;

class UserDashboard extends Component
{
    public $openTickets;
    public $stats = [];

    public function mount()
    {
        $this->refresh();
    }

    public function refresh()
    {
        $user = auth()->user();
        
        $this->openTickets = Ticket::where('requester_id', $user->id)
            ->whereNotIn('status', ['closed', 'resolved'])
            ->latest()
            ->take(5)
            ->get();

        $this->stats = [
            'open' => Ticket::where('requester_id', $user->id)
                ->whereNotIn('status', ['closed', 'resolved'])
                ->count(),
            'resolved' => Ticket::where('requester_id', $user->id)
                ->whereIn('status', ['closed', 'resolved'])
                ->count(),
        ];
    }

    public function render()
    {
        return view('livewire.portal.user-dashboard');
    }
}
