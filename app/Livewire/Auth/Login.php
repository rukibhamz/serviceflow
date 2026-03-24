<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.auth')]
class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function login(): void
    {
        $this->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            $this->addError('email', 'These credentials do not match our records.');
            return;
        }

        session()->regenerate();

        $user = Auth::user();

        if ($user->hasRole('admin') || $user->hasRole('agent') || $user->role === 'admin' || $user->role === 'agent') {
            $this->redirect(route('agent.tickets.index'), navigate: true);
        } else {
            $this->redirect(route('portal.index'), navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
