<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Installer\CpanelPipeInstaller;
use App\Services\Installer\DatabaseInstaller;
use App\Services\Installer\EnvironmentChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InstallerController extends Controller
{
    public function index(): View
    {
        $checker = new EnvironmentChecker();
        $results = $checker->check();
        $allPassed = $checker->allPassed();

        return view('installer.index', compact('results', 'allPassed'));
    }

    public function database(): View
    {
        return view('installer.database');
    }

    public function testDatabase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'connection' => ['required', 'in:mysql,pgsql'],
            'host'       => ['required', 'string'],
            'port'       => ['required', 'numeric'],
            'database'   => ['required', 'string'],
            'username'   => ['required', 'string'],
            'password'   => ['nullable', 'string'],
        ]);

        $installer = new DatabaseInstaller();

        if ($installer->testConnection($validated)) {
            return response()->json(['success' => true, 'message' => 'Connection successful.']);
        }

        return response()->json(['success' => false, 'message' => 'Could not connect. Check your credentials.'], 422);
    }

    public function storeDatabase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'connection' => ['required', 'in:mysql,pgsql'],
            'host'       => ['required', 'string'],
            'port'       => ['required', 'numeric'],
            'database'   => ['required', 'string'],
            'username'   => ['required', 'string'],
            'password'   => ['nullable', 'string'],
        ]);

        $installer = new DatabaseInstaller();

        if (! $installer->testConnection($validated)) {
            return back()->withErrors(['connection' => 'Could not connect to the database. Please check your credentials.'])->withInput();
        }

        $installer->install($validated);

        return redirect()->route('installer.account');
    }

    public function account(): View
    {
        return view('installer.account');
    }

    public function storeAccount(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'admin',
        ]);

        $user->assignRole('admin');

        return redirect()->route('installer.finish');
    }

    public function finish(): View
    {
        $dbInstaller = new DatabaseInstaller();
        $dbInstaller->writeEnvValue('APP_INSTALLED', 'true');
        file_put_contents(storage_path('install.lock'), 'Installed on ' . now()->toDateTimeString());

        $pipeInstaller = new CpanelPipeInstaller();
        $forwardContent = $pipeInstaller->getForwardFileContent('support@yourdomain.com');
        $pipeScriptPath = $pipeInstaller->getPipeScriptPath();

        return view('installer.finish', compact('forwardContent', 'pipeScriptPath'));
    }
}
