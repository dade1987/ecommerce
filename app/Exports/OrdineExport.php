<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdineExport implements FromArray, WithTitle, WithStyles
{
    protected $ordine;

    public function __construct(array $ordine)
    {
        $this->ordine = $ordine;
    }

    public function array(): array
    {
        $cliente = $this->ordine['cliente'];
        $dest = $this->ordine['destinazione_merce'];
        $articoli = $this->ordine['dettagli_articoli'];
        $output = [
            ['Nome Cliente', '', $cliente['nome']],
            ['Destinazione', '', $dest['indirizzo']],
            ['Data Ordine', '', $this->ordine['data_ordine']],
            ['Data Consegna', '', $this->ordine['data_consegna']],
            ['Numero Ordine', '', str_replace('/', ' ', $this->ordine['numero_ordine'])],
            ['Persona Contatto', '', $dest['referente']],
            [''],
            [''],
        ];

        foreach ($articoli as $articolo) {
            $quantita = $articolo['quantita_per_taglia'];
            $taglie = array_keys($quantita);
            $quantita_valori = array_values($quantita);

            $output[] = ['Matricola', '', $articolo['matricola'] ?? '', 'Marcatura', '', $articolo['descrizione'] ?? '', '', '', '', 'Calzata', $articolo['calzata'] ?? ''];
            $output[] = ['Taglie',  ...$taglie];
            $output[] = ['Quantità', ...$quantita_valori];
            $output[] = ['Note di produzione', ($articolo['note_di_produzione'] === 'N/A') ? '' : ($articolo['note_di_produzione'] ?? '')];
            $output[] = [''];
        }

        return $output;

    }

    public function styles(Worksheet $sheet)
    {
        // Ottieni l'intervallo di celle utilizzate nel foglio
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Applica il grassetto alla prima colonna di tutte le righe
        for ($row = 1; $row <= $lastRow; $row++) {
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
        }

        // Imposta la larghezza delle colonne per una migliore leggibilità
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(10);
        $sheet->getColumnDimension('C')->setWidth(20);

        return [];
    }

    public function title(): string
    {
        return 'Ordine';
    }
}
