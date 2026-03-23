<?php

namespace App\Services\Asset;

use App\Models\Asset;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Bulk-imports assets from a CSV/XLSX file.
 *
 * Expected columns (heading row):
 *   name, type, serial_number, asset_tag, status, purchased_at
 *
 * Returns an ImportResult with created count and per-row errors.
 */
class AssetImporter implements ToCollection, WithHeadingRow
{
    /** @var array<int, array<string, string>> */
    private array $errors = [];

    private int $created = 0;

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // +2 because row 1 is the heading
            $data      = $row->toArray();

            $validator = Validator::make($data, [
                'name'          => 'required|string|max:255',
                'type'          => 'required|string|max:100',
                'serial_number' => 'nullable|string|max:255',
                'asset_tag'     => 'nullable|string|max:100',
                'status'        => 'nullable|string|in:in_use,available,retired,in_repair,disposed',
                'purchased_at'  => 'nullable|date',
            ]);

            if ($validator->fails()) {
                $this->errors[$rowNumber] = $validator->errors()->all();
                continue;
            }

            Asset::create([
                'name'          => $data['name'],
                'type'          => $data['type'],
                'serial_number' => $data['serial_number'] ?? null,
                'asset_tag'     => $data['asset_tag'] ?? null,
                'status'        => $data['status'] ?? 'available',
                'purchased_at'  => $data['purchased_at'] ?? null,
            ]);

            $this->created++;
        }
    }

    /**
     * Import from an uploaded file path.
     *
     * @param  string  $filePath  Absolute or storage-relative path
     * @return ImportResult
     */
    public function import(string $filePath): ImportResult
    {
        Excel::import($this, $filePath);

        return new ImportResult($this->created, $this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function createdCount(): int
    {
        return $this->created;
    }
}
