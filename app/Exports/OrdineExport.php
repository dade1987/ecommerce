<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdineExport implements FromArray, WithTitle, WithStyles
{
    protected $datiOrdine;

    public function __construct(array $datiOrdine)
    {
        $this->datiOrdine = $datiOrdine;
    }

    public function array(): array
    {
        $fornitore = $this->datiOrdine['fornitore'];
        $cliente = $this->datiOrdine['cliente'];
        $ordine = $this->datiOrdine['ordine'];
        $articoli = $ordine['articoli'];

        $output = [
            ['Fornitore', $fornitore['nome']],
            ['Indirizzo Fornitore', $fornitore['indirizzo']],
            ['Contatti Fornitore', ($fornitore['contatti']['email'] ?? '') . ' - ' . ($fornitore['contatti']['telefono'] ?? '')],
            [], // Riga vuota
            ['Cliente', $cliente['nome']],
            ['Indirizzo Consegna', $cliente['indirizzo']],
            [], // Riga vuota
            ['Data Ordine', $ordine['data_ordine']],
            ['Numero Ordine', $ordine['numero_ordine']],
            ['Termini di Consegna', $ordine['termini_consegna']],
            [], // Riga vuota
            // Intestazioni per la tabella degli articoli
            ['Codice Articolo', 'Descrizione', 'Quantità', 'Prezzo Unitario (€)', 'Totale (€)'],
        ];

        foreach ($articoli as $articolo) {
            $output[] = [
                $articolo['codice'],
                $articolo['descrizione'],
                $articolo['quantita'],
                $articolo['prezzo_unitario'],
                $articolo['totale'],
            ];
        }

        // Riga vuota prima del totale
        $output[] = [];
        // Riga del totale ordine
        $output[] = ['', '', '', 'Totale Ordine', $ordine['totale_ordine']];

        return $output;
    }

    public function styles(Worksheet $sheet)
    {
        // Trova l'indice dell'ultima riga
        $lastRow = $sheet->getHighestRow();
        
        // Indice della riga di intestazione della tabella articoli (è fissa)
        $headerRowIndex = 12;

        // Applica il grassetto alla prima colonna delle informazioni di testata
        for ($row = 1; $row < $headerRowIndex; $row++) {
            $sheet->getStyle('A'.$row)->getFont()->setBold(true);
        }
        
        // Grassetto per l'intera riga di intestazione della tabella articoli
        $sheet->getStyle('A'.$headerRowIndex.':E'.$headerRowIndex)->getFont()->setBold(true);

        // Grassetto per l'etichetta del totale ordine
        $sheet->getStyle('D'.$lastRow)->getFont()->setBold(true);

        // Imposta larghezza colonne per migliore leggibilità
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);

        // Formattazione valuta per le colonne di prezzo e totale degli articoli
        $firstArticleRow = $headerRowIndex + 1;
        $lastArticleRow = $lastRow - 2; // Sottrai la riga vuota e la riga del totale

        if ($lastArticleRow >= $firstArticleRow) {
            $sheet->getStyle('D'.$firstArticleRow.':E'.$lastArticleRow)
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }

        // Formato valuta per il totale generale dell'ordine
        $sheet->getStyle('E'.$lastRow)
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

        return [];
    }

    public function title(): string
    {
        return 'Ordine';
    }
}
