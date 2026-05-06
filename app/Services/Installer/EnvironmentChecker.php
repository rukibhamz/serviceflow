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
        $envPath     = base_path('.env');
        $examplePath = base_path('.env.example');

        if (file_exists($envPath)) {
            return [
                'name'    => '.env File',
                'status'  => 'pass',
                'message' => '.env file exists',
            ];
        }

        if (file_exists($examplePath)) {
            return [
                'name'    => '.env File',
                'status'  => 'warn',
                'message' => '.env not found but .env.example is available — it will be copied during installation',
            ];
        }

        return [
            'name'    => '.env File',
            'status'  => 'fail',
            'message' => 'Neither .env nor .env.example found',
        ];
    }

    private function checkAppKey(): array
    {
        $key = env('APP_KEY', '');

        if (! empty($key)) {
            return [
                'name'    => 'APP_KEY',
                'status'  => 'pass',
                'message' => 'APP_KEY is set',
            ];
        }

        return [
            'name'    => 'APP_KEY',
            'status'  => 'warn',
            'message' => 'APP_KEY is not set — it will be generated during installation',
        ];
    }
}
