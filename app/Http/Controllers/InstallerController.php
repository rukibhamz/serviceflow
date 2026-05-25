<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Installer\CpanelPipeInstaller;
use App\Services\Installer\DatabaseInstaller;
use App\Services\Installer\EnvironmentChecker;
use App\Services\SettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

        set_time_limit(300);

        try {
            $installer->install($validated);
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors([
                    'connection' => 'Database setup failed: '.$e->getMessage()
                        .' Check _internal_storage/logs/laravel.log on the server for details.',
                ])
                ->withInput();
        }

        return redirect()->route('installer.branding');
    }

    public function branding(): View|RedirectResponse
    {
        if (! Schema::hasTable('settings')) {
            return redirect()->route('installer.database')
                ->withErrors(['connection' => 'Complete database setup before configuring branding.']);
        }

        $settings = app(SettingService::class);
        $all = $settings->all();
        $presets = SettingService::presets();

        return view('installer.branding', [
            'presets'    => $presets,
            'curName'    => old('brand_name', $all['brand_name'] ?? config('app.name', 'ServiceFlow')),
            'curPreset'  => old('theme_preset', $all['theme_preset'] ?? 'blue'),
            'curPrimary' => old('theme_primary', $all['theme_primary'] ?? '#1a4fa0'),
            'curAccent'  => old('theme_accent', $all['theme_accent'] ?? '#f97316'),
        ]);
    }

    public function storeBranding(Request $request, SettingService $settings): RedirectResponse
    {
        if (! Schema::hasTable('settings')) {
            return redirect()->route('installer.database')
                ->withErrors(['connection' => 'Complete database setup before configuring branding.']);
        }

        $data = $request->validate([
            'brand_name'    => ['required', 'string', 'max:80'],
            'theme_preset'  => ['required', 'string', 'in:'.implode(',', array_keys(SettingService::presets()))],
            'theme_primary' => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'theme_accent'  => ['required', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ]);

        if ($request->hasFile('brand_logo')) {
            $request->validate(['brand_logo' => ['image', 'max:2048']]);
            $settings->uploadLogo($request->file('brand_logo'));
        }

        if ($request->hasFile('brand_favicon')) {
            $request->validate(['brand_favicon' => ['file', 'mimes:ico,png,jpg,jpeg,svg', 'max:512']]);
            $settings->uploadFavicon($request->file('brand_favicon'));
        }

        $settings->set([
            'brand_name'    => $data['brand_name'],
            'theme_preset'  => $data['theme_preset'],
            'theme_primary' => $data['theme_primary'],
            'theme_accent'  => $data['theme_accent'],
        ]);

        $installer = new DatabaseInstaller();
        $installer->writeEnvValue('APP_NAME', $data['brand_name']);

        return redirect()->route('installer.account');
    }

    public function account(): View|RedirectResponse
    {
        if (! Schema::hasTable('settings')) {
            return redirect()->route('installer.database');
        }

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
        $dbInstaller->finalizeSessionDriver();
        SettingService::purgeCachedData();
        file_put_contents(storage_path('install.lock'), 'Installed on ' . now()->toDateTimeString());

        $pipeInstaller = new CpanelPipeInstaller();
        $forwardContent = $pipeInstaller->getForwardFileContent('support@yourdomain.com');
        $pipeScriptPath = $pipeInstaller->getPipeScriptPath();

        return view('installer.finish', compact('forwardContent', 'pipeScriptPath'));
    }
}
