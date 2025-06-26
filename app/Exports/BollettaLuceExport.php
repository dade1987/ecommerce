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
        // Se i dati principali sono annidati (es. dentro una chiave 'bolletta'), li usiamo.
        $this->data = isset($exportData['bolletta']) && is_array($exportData['bolletta']) ? [$exportData['bolletta']] : [$exportData];
        $this->buildDynamicHeadings();
    }

    public function collection(): Collection
    {
        return new Collection($this->data);
    }

    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * @param mixed $row
     */
    public function map($row): array
    {
        return [
            data_get($row, 'fornitore', 'N/A'),
            data_get($row, 'numero_documento', 'N/A'),
            data_get($row, 'periodo_riferimento', 'N/A'),
            data_get($row, 'nome_cliente', 'N/A'),
            data_get($row, 'indirizzo_fornitura', 'N/A'),
            data_get($row, 'codice_fiscale', 'N/A'),
            data_get($row, 'codice_cliente', 'N/A'),
            data_get($row, 'codice_pod', 'N/A'),
            data_get($row, 'tipologia_contratto', 'N/A'),
            data_get($row, 'potenza_disponibile', 'N/A'),
            data_get($row, 'livello_tensione', 'N/A'),
            data_get($row, 'data_emissione', 'N/A'),
            data_get($row, 'data_scadenza', 'N/A'),
            data_get($row, 'importo_totale', 'N/A'),
            data_get($row, 'consumo_energia_kwh', 'N/A'),
            data_get($row, 'costo_medio_unitario_kwh', 'N/A'),
            data_get($row, 'dettaglio_costi.energia', 'N/A'),
            data_get($row, 'dettaglio_costi.trasporto_gestione_contatore', 'N/A'),
            data_get($row, 'dettaglio_costi.oneri_sistema', 'N/A'),
            data_get($row, 'dettaglio_costi.imposte_iva', 'N/A'),
            data_get($row, 'letture.precedente', 'N/A'),
            data_get($row, 'letture.attuale', 'N/A'),
            data_get($row, 'informazioni_tecniche.distributore_competente', 'N/A'),
            data_get($row, 'modalita_pagamento', 'N/A'),
            data_get($row, 'contatti_servizio_clienti.telefono', 'N/A'),
            data_get($row, 'contatti_servizio_clienti.sito_web', 'N/A'),
        ];
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
        foreach ($this->data as $row) {
            $headings = array_merge($headings, array_keys($this->flatten_array($row)));
        }
        $this->headings = array_values(array_unique($headings));
    }

    private function flatten_array(array $array, string $prefix = '', string $separator = '_'): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value) && !empty($value)) {
                $result = array_merge($result, $this->flatten_array($value, $prefix . $key . $separator, $separator));
            } else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }
} 