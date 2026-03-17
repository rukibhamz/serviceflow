<?php

namespace App\Services\Installer;

class CpanelPipeInstaller
{
    public function getPipeScriptPath(): string
    {
        return base_path('scripts/pipe.php');
    }

    public function getForwardFileContent(string $email): string
    {
        $phpBinary  = PHP_BINARY;
        $scriptPath = $this->getPipeScriptPath();

        return "|{$phpBinary} {$scriptPath}";
    }

    public function generatePipeScript(): string
    {
        $appPath = base_path();

        return "#!/usr/bin/env php\n<?php\n\$input = file_get_contents('php://stdin');\n\$appPath = '{$appPath}';\npassthru(\"php {\$appPath}/artisan email:ingest\", \$exitCode);\nexit(\$exitCode);\n";
    }
}
