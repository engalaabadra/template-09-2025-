<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use JetBrains\PhpStorm\NoReturn;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

/**
 * Class ExcelExport
 *
 * A reusable export class using Maatwebsite\Excel to generate Excel files.
 * Supports:
 * - Dynamic column headers with optional translations.
 * - Nested key mapping (e.g., 'user.name').
 * - Closure-based custom column values.
 * - Boolean field translations (yes/no).
 *
 * Example usage:
 * ExcelExport::download('report.xlsx', [
 *     'ID' => 'id',
 *     'User Name' => 'user.name',
 *     'Is Active' => 'is_active',
 *     'Custom Column' => fn($row) => strtoupper($row['custom_field']),
 * ], $collection);
 */
class ExcelExport implements FromCollection, WithHeadings, WithMapping, WithEvents
{
    use Exportable; // Trait from Maatwebsite\Excel for export helpers

    private const TRANS_HEADER_KEY = 'column'; // Translation file key prefix for headers
    private const BOOLEAN_FIELDS = ['is_active']; // Fields to translate as Yes/No

    private Collection $collection; // Data to export
    private array $headers; // Formatted column headers
    private array $mappings; // Column data mappings

    /**
     * Map example:
     * [
     *   'id',                              // Will use translated header if exists
     *   'user.name',                       // Nested key access
     *   'Column Header' => 'column_name',  // Custom header => field mapping
     *   'Custom' => fn($row) => 'Value',   // Closure to calculate value
     * ]
     */
    public function __construct(Collection $collection, array $map)
    {
        $this->collection = $collection;     // Store provided dataset
        $this->processMap($map);             // Process mapping array into headers + mappings
    }

    // Process headers and mappings
    private function processMap(array $map): void
    {
        // Ensure 'id' and 'created_at_text' are always included in export columns
        if (!in_array('id', $map)) {
            array_unshift($map, 'id'); // Add 'id' at the beginning of the array
        }

        // to add created_at_text in all proccess export without need pass from export col. model
        if (!in_array('created_at_text', $map)) {
            $map[] = 'created_at_text';
        }
        $headers = [];
        $mappings = [];

        foreach ($map as $key => $value) {
            // If numeric key, use the value as header; else use the key as custom header
            $headers[] = is_int($key) ? $this->formatTitle($value) : $this->formatTitle($key);
            $mappings[] = $value; // Store mapping for later
        }

        $this->headers = $headers;
        $this->mappings = $mappings;
    }

    // Required by FromCollection — returns the raw data collection
    public function collection(): Collection
    {
        return $this->collection;
    }

    // Format header titles, translate if possible
    private function formatTitle(string $title): string
    {
        $title = str_replace('_text', '', $title); // Remove _text suffix if present
        $lastDotPosition = strrpos($title, '.');   // Find last dot for nested keys

        if ($lastDotPosition !== false) {
            $title = substr($title, $lastDotPosition + 1); // Keep only last segment
        }

        $translatedTitle = __(self::TRANS_HEADER_KEY . ".$title"); // Try translation
        return str_contains($translatedTitle, self::TRANS_HEADER_KEY) ? $title : $translatedTitle;
    }

    // Required by WithHeadings — returns header row
    public function headings(): array
    {
        return $this->headers;
    }

    // Required by WithMapping — maps each row's values based on $mappings
    #[NoReturn]
    public function map($row): array
    {
        return array_map(function ($mapping) use ($row) {
            if (is_callable($mapping)) {
                return $mapping($row); // Run closure if mapping is a function
            }
            return $this->resolveValue($row, $mapping); // Resolve string path
        }, $this->mappings);
    }

    // Resolve nested keys and handle boolean translation
    private function resolveValue($data, string $path)
    {
        $value = $data;
        foreach (explode('.', $path) as $segment) {
            $value = $value[$segment] ?? null; // Navigate nested arrays

            // If the segment is a boolean field, translate Yes/No
            if (in_array($segment, self::BOOLEAN_FIELDS)) {
                return $value == 1 ? __('message.yes') : __('message.no');
            }
        }
        return $value;
    }

    // Required by WithEvents — allows modifying sheet after creation
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Set right-to-left if app locale is Arabic
                $event->sheet->getDelegate()->setRightToLeft(app()->isLocal() == 'ar');
            },
        ];
    }
}
