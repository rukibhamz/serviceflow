<?php

namespace App\Services\Installer;

use Illuminate\Support\Facades\Artisan;
use PDO;
use PDOException;

class DatabaseInstaller
{
    public function testConnection(array $config): bool
    {
        try {
            $dsn = $this->buildDsn($config);
            new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            return true;
        } catch (PDOException) {
            return false;
        }
    }

    public function install(array $config): void
    {
        $this->writeEnvValue('DB_CONNECTION', $config['connection'] ?? 'mysql');
        $this->writeEnvValue('DB_HOST', $config['host'] ?? '127.0.0.1');
        $this->writeEnvValue('DB_PORT', $config['port'] ?? '3306');
        $this->writeEnvValue('DB_DATABASE', $config['database'] ?? '');
        $this->writeEnvValue('DB_USERNAME', $config['username'] ?? '');
        $this->writeEnvValue('DB_PASSWORD', $config['password'] ?? '');

        // Switch session driver to database now that DB is configured
        $this->writeEnvValue('SESSION_DRIVER', 'database');

        Artisan::call('config:clear');
        // First-time schema only. Existing deployments upgrade with `php artisan migrate --force` (see docs/UPGRADE.md).
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('db:seed', ['--force' => true]);
    }

    public function writeEnvValue(string $key, string $value): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $examplePath = base_path('.env.example');
            if (file_exists($examplePath)) {
                copy($examplePath, $envPath);
            } else {
                file_put_contents($envPath, '');
            }
        }

        $contents = file_get_contents($envPath);

        // Escape value if it contains spaces or special characters
        $escapedValue = str_contains($value, ' ') ? "\"{$value}\"" : $value;

        if (preg_match("/^{$key}=/m", $contents)) {
            $contents = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$escapedValue}",
                $contents
            );
        } else {
            $contents .= PHP_EOL . "{$key}={$escapedValue}";
        }

        file_put_contents($envPath, $contents);
    }

    private function buildDsn(array $config): string
    {
        $connection = $config['connection'] ?? 'mysql';
        $host       = $config['host'] ?? '127.0.0.1';
        $port       = $config['port'] ?? '3306';
        $database   = $config['database'] ?? '';

        return match ($connection) {
            'pgsql'  => "pgsql:host={$host};port={$port};dbname={$database}",
            default  => "mysql:host={$host};port={$port};dbname={$database}",
        };
    }
}
