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
            $row['fornitore'] ?? 'N/A',
            $row['numero_documento'] ?? 'N/A',
            $row['periodo_riferimento'] ?? 'N/A',
            $row['nome_cliente'] ?? 'N/A',
            $row['indirizzo_fornitura'] ?? 'N/A',
            $row['codice_fiscale'] ?? 'N/A',
            $row['codice_cliente'] ?? 'N/A',
            $row['codice_pod'] ?? 'N/A',
            $row['tipologia_contratto'] ?? 'N/A',
            $row['potenza_disponibile'] ?? 'N/A',
            $row['livello_tensione'] ?? 'N/A',
            $row['data_emissione'] ?? 'N/A',
            $row['data_scadenza'] ?? 'N/A',
            $row['importo_totale'] ?? 'N/A',
            $row['consumo_energia_kwh'] ?? 'N/A',
            $row['costo_medio_unitario_kwh'] ?? 'N/A',
            $row['dettaglio_costi']['energia'] ?? 'N/A',
            $row['dettaglio_costi']['trasporto_gestione_contatore'] ?? 'N/A',
            $row['dettaglio_costi']['oneri_sistema'] ?? 'N/A',
            $row['dettaglio_costi']['imposte_iva'] ?? 'N/A',
            $row['letture']['precedente'] ?? 'N/A',
            $row['letture']['attuale'] ?? 'N/A',
            $row['informazioni_tecniche']['distributore_competente'] ?? 'N/A',
            $row['modalita_pagamento'] ?? 'N/A',
            $row['contatti_servizio_clienti']['telefono'] ?? 'N/A',
            $row['contatti_servizio_clienti']['sito_web'] ?? 'N/A',
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