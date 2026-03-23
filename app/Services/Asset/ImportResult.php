<?php

namespace App\Services\Asset;

/**
 * Value object returned by AssetImporter::import().
 */
readonly class ImportResult
{
    /**
     * @param  int                                    $created  Number of rows successfully imported
     * @param  array<int, array<string, string>>      $errors   Row-number → validation error messages
     */
    public function __construct(
        public int $created,
        public array $errors,
    ) {}

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }

    public function errorCount(): int
    {
        return count($this->errors);
    }
}
