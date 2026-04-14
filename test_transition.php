<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$machine = app(\App\Services\Tickets\TicketStatusMachine::class);

try {
    $machine->transition(new \App\Models\Ticket(['status' => 'open']), 'resolved');
    echo "Success!\n";
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
