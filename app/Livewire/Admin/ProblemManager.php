<?php

namespace App\Livewire\Admin;

use App\Models\Ticket;
use Livewire\Component;

class ProblemManager extends Component
{
    public function render()
    {
        $stats = [
            'total' => Ticket::where('type', 'problem')->count(),
            'open' => Ticket::where('type', 'problem')->whereNotIn('status', ['resolved', 'closed'])->count(),
            'known_errors' => Ticket::where('type', 'problem')->whereJsonContains('custom_fields->known_error', true)->count(),
        ];

        return view('livewire.admin.problem-manager', compact('stats'))
            ->layout('layouts.admin');
    }
}
