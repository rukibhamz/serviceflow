<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$users = [
    ['email' => 'admin@example.com', 'name' => 'Admin User', 'role' => 'admin'],
    ['email' => 'agent@example.com', 'name' => 'Agent User', 'role' => 'agent'],
    ['email' => 'user@example.com',  'name' => 'End User',   'role' => 'end_user'],
];

foreach ($users as $u) {
    $record = User::updateOrCreate(
        ['email' => $u['email']],
        [
            'name'      => $u['name'],
            'password'  => Hash::make('password'),
            'role'      => $u['role'],
            'is_active' => true,
        ]
    );
    $record->syncRoles([$u['role']]);
    echo "Created/updated: {$u['email']} (role: {$u['role']})\n";
}

echo "\nDone! All test users ready.\n";
