<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Illuminate\Support\Collection;

class OrdineExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    use Exportable;

    protected array $data;
    protected array $headings = [];
    protected array $processedData = [];

    public function __construct(array $exportData)
    {
        $this->data = $this->processInputData($exportData);
        $this->buildDynamicHeadings();
    }

    private function processInputData(array $input): array
    {
        $processed = [];
        $prodotti = data_get($input, 'prodotti', []);

        if (empty($prodotti)) {
            $processed[] = $input;
            return $processed;
        }

        foreach ($prodotti as $prodotto) {
            $row = $input;
            unset($row['prodotti']); // Rimuovo l'array prodotti per evitare duplicazioni
            $row['prodotto'] = $prodotto;
            $processed[] = $row;
        }
        return $processed;
    }

    public function collection(): Collection
    {
        return new Collection($this->data);
    }

    public function headings(): array
    {
        return array_map('ucfirst', str_replace(['_', '.'], ' ', $this->headings));
    }

    public function map($row): array
    {
        $mappedRow = [];
        foreach ($this->headings as $heading) {
            $mappedRow[] = data_get($row, str_replace('_', '.', $heading), 'N/A');
        }
        return $mappedRow;
    }

    public function title(): string
    {
        return 'Ordine';
    }

    private function buildDynamicHeadings(): void
    {
        if (!empty($this->data[0])) {
            $this->headings = array_keys($this->flatten_array($this->data[0]));
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
