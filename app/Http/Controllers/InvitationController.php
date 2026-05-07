<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InvitationController extends Controller
{
    public function show(string $token): View|RedirectResponse
    {
        $invitation = UserInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();

        return view('auth.accept-invitation', compact('invitation'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = UserInvitation::where('token', $token)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->firstOrFail();
        $inviter = User::find($invitation->invited_by);

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        $role = $invitation->role === 'user' ? 'end_user' : $invitation->role;

        $user = User::create([
            'tenant_id' => $inviter?->tenant_id,
            'name'      => $validated['name'],
            'email'     => $invitation->email,
            'password'  => Hash::make($validated['password']),
            'role'      => $role,
            'is_active' => true,
        ]);

        // Assign Spatie role
        $user->assignRole($role);

        // Mark invitation as accepted
        $invitation->update(['accepted_at' => now()]);

        Auth::login($user);

        return redirect('/');
    }
}
