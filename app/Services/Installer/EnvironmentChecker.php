<?php

namespace App\Services\Installer;

class EnvironmentChecker
{
    private const REQUIRED_EXTENSIONS = [
        'PDO', 'pdo_mysql', 'mbstring', 'openssl',
        'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'gd',
    ];

    public function check(): array
    {
        return [
            $this->checkPhpVersion(),
            ...$this->checkExtensions(),
            $this->checkDirectoryWritable('_internal_storage', storage_path()),
            $this->checkDirectoryWritable('bootstrap/cache', base_path('bootstrap/cache')),
            $this->checkEnvFile(),
            $this->checkAppKey(),
        ];
    }

    public function allPassed(): bool
    {
        foreach ($this->check() as $result) {
            if ($result['status'] === 'fail') {
                return false;
            }
        }

        return true;
    }

    private function checkPhpVersion(): array
    {
        $required = '8.2.0';
        $current  = PHP_VERSION;
        $pass     = version_compare($current, $required, '>=');

        return [
            'name'    => 'PHP Version',
            'status'  => $pass ? 'pass' : 'fail',
            'message' => $pass
                ? "PHP {$current} meets the minimum requirement (>= {$required})"
                : "PHP {$current} is below the minimum requirement (>= {$required})",
        ];
    }

    private function checkExtensions(): array
    {
        $results = [];

        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            $loaded = extension_loaded($ext);
            $results[] = [
                'name'    => "Extension: {$ext}",
                'status'  => $loaded ? 'pass' : 'fail',
                'message' => $loaded ? "{$ext} is loaded" : "{$ext} extension is missing",
            ];
        }

        return $results;
    }

    private function checkDirectoryWritable(string $label, string $path): array
    {
        $writable = is_writable($path);

        return [
            'name'    => "Writable: {$label}",
            'status'  => $writable ? 'pass' : 'fail',
            'message' => $writable
                ? "{$path} is writable"
                : "{$path} is not writable — please chmod 775",
        ];
    }

    private function checkEnvFile(): array
    {
        $envPath = base_path('.env');

        return [
            'name'    => '.env File',
            'status'  => file_exists($envPath) ? 'pass' : 'fail',
            'message' => file_exists($envPath)
                ? '.env file exists (auto-created from .env.example)'
                : '.env file could not be created — check file system permissions',
        ];
    }

    private function checkAppKey(): array
    {
        $key = env('APP_KEY', '');

        return [
            'name'    => 'APP_KEY',
            'status'  => ! empty($key) ? 'pass' : 'fail',
            'message' => ! empty($key)
                ? 'APP_KEY is set (auto-generated)'
                : 'APP_KEY could not be generated — check file system permissions',
        ];
    }
}
