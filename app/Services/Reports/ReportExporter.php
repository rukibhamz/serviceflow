<?php

namespace App\Services\Reports;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Exports report datasets to PDF or Excel.
 */
class ReportExporter
{
    /**
     * Export a flat collection of rows to an Excel download response.
     *
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  string[]                               $headings
     */
    public function toExcel(Collection $rows, array $headings, string $filename = 'report.xlsx'): BinaryFileResponse
    {
        $export = new class ($rows, $headings) implements FromCollection, WithHeadings {
            public function __construct(
                private readonly Collection $rows,
                private readonly array $headings,
            ) {}

            public function collection(): Collection
            {
                return $this->rows->map(fn ($row) => is_array($row) ? $row : (array) $row);
            }

            public function headings(): array
            {
                return $this->headings;
            }
        };

        return Excel::download($export, $filename);
    }

    /**
     * Export a dataset to a PDF download response using a Blade view.
     *
     * @param  array<string, mixed>  $data   Data passed to the view
     * @param  string                $view   Blade view path (e.g. 'reports.ticket-volume')
     */
    public function toPdf(array $data, string $view, string $filename = 'report.pdf'): Response
    {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', 'landscape');

        return $pdf->download($filename);
    }

    /**
     * Flatten a report array (as returned by ReportBuilder) into a Collection
     * suitable for Excel export.
     *
     * @param  Collection|\Illuminate\Database\Eloquent\Collection  $collection
     * @return Collection<int, array<string, mixed>>
     */
    public function flattenCollection(iterable $collection): Collection
    {
        return collect($collection)->map(fn ($row) => is_array($row) ? $row : (array) $row);
    }
}
