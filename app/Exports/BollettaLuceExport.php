<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BollettaLuceExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected array $data;
    protected array $headings = [];

    public function __construct(array $exportData)
    {
        $this->data = isset($exportData['bolletta']) && is_array($exportData['bolletta']) ? [$exportData['bolletta']] : [$exportData];
        $this->buildDynamicHeadings();
    }

    public function collection(): Collection
    {
        return new Collection($this->data);
    }

    public function headings(): array
    {
        return array_map('ucfirst', str_replace('_', ' ', $this->headings));
    }

    public function map($row): array
    {
        $mappedRow = [];
        foreach ($this->headings as $heading) {
            $mappedRow[] = data_get($row, str_replace('_', '.', $heading), 'N/A');
        }
        return $mappedRow;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('1')->getFont()->setBold(true);
        foreach (range('A', $sheet->getHighestColumn()) as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        return [];
    }

    private function buildDynamicHeadings(): void
    {
        $headings = [];
        if (!empty($this->data[0])) {
            $flattened = $this->flatten_array($this->data[0]);
            $this->headings = array_keys($flattened);
        }
    }

    private function flatten_array(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix ? $prefix . '_' . $key : $key;
            if (is_array($value) && !empty($value)) {
                $result = array_merge($result, $this->flatten_array($value, $newKey));
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }
} 