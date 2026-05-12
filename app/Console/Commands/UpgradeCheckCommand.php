<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;

class UpgradeCheckCommand extends Command
{
    protected $signature = 'serviceflow:upgrade-check
                            {--database= : The database connection to use}
                            {--fail-on-pending : Exit with status 1 when migrations are pending (CI / deploy gates)}';

    protected $description = 'List pending migrations and remind operators to run migrate';

    public function handle(): int
    {
        /** @var Migrator $migrator */
        $migrator = app('migrator');

        $connection = $this->option('database');

        return (int) $migrator->usingConnection($connection, function () use ($migrator) {
            if (! $migrator->repositoryExists()) {
                $this->components->error('Migration table not found. Complete installation or run migrations once to create it.');

                return self::FAILURE;
            }

            $files = $migrator->getMigrationFiles($migrator->paths());
            $ran = $migrator->getRepository()->getRan();

            $pending = collect($files)
                ->keys()
                ->reject(fn (string $name) => in_array($name, $ran, true))
                ->values()
                ->all();

            if ($pending === []) {
                $this->components->info('Database schema matches migrations (no pending migrations).');

                return self::SUCCESS;
            }

            $count = count($pending);
            $this->components->warn("{$count} pending migration".($count === 1 ? '' : 's').':');
            foreach ($pending as $name) {
                $this->line('  <fg=yellow>'.$name.'</>');
            }
            $this->newLine();
            $this->components->comment('Run on this environment: php artisan migrate --force');

            return $this->option('fail-on-pending') ? self::FAILURE : self::SUCCESS;
        });
    }
}
