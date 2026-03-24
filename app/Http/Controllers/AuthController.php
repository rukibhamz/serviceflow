<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->hasRole('admin') || $user->hasRole('agent') || in_array($user->role, ['admin', 'agent'])) {
            return redirect()->intended(route('agent.dashboard'));
        }

        return redirect()->intended(route('portal.index'));
    }
}
