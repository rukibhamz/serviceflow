#!/usr/bin/env php
<?php
$input = file_get_contents('php://stdin');
$appPath = dirname(__DIR__);
passthru("php {$appPath}/artisan email:ingest", $exitCode);
exit($exitCode);
