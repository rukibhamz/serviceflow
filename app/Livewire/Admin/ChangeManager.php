<?php

namespace App\Livewire\Admin;

use App\Models\Ticket;
use Livewire\Component;

class ChangeManager extends Component
{
    public function render()
    {
        $stats = [
            'total' => Ticket::where('type', 'change')->count(),
            'pending' => Ticket::where('type', 'change')->where('status', 'pending_approval')->count(),
            'approved' => Ticket::where('type', 'change')->where('status', 'approved')->count(),
            'scheduled' => Ticket::where('type', 'change')->where('status', 'scheduled')->count(),
        ];

        $changes = Ticket::where('type', 'change')
            ->with('requester')
            ->latest()
            ->take(20)
            ->get();

        return view('livewire.admin.change-manager', compact('stats', 'changes'))
            ->layout('layouts.admin');
    }
}
