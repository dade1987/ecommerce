<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class OrdineExport implements FromArray, WithTitle, WithStyles
{
    protected array $dati;
    protected string $locale;

    public function __construct(array $exportData, string $locale = 'it')
    {
        $this->dati = $exportData;
        $this->locale = $locale;
    }

    public function array(): array
    {
        $fornitoreNome = data_get($this->dati, 'fornitore.nome', 'N/A');
        $fornitoreIndirizzo = data_get($this->dati, 'fornitore.indirizzo', 'N/A');
        $fornitoreEmail = data_get($this->dati, 'fornitore.contatti.email', '');
        $fornitoreTel = data_get($this->dati, 'fornitore.contatti.telefono', '');
        $fornitoreContatti = trim("{$fornitoreEmail} " . ($fornitoreEmail && $fornitoreTel ? '- ' : '') . "{$fornitoreTel}");
        if (empty($fornitoreContatti)) $fornitoreContatti = 'N/A';
        
        $clienteNome = data_get($this->dati, 'cliente.nome', 'N/A');
        $clienteIndirizzo = data_get($this->dati, 'cliente.indirizzo_consegna', 'N/A');
        
        $ordineData = data_get($this->dati, 'ordine.data_ordine', 'N/A');
        $ordineNumero = data_get($this->dati, 'ordine.numero_ordine', 'N/A');
        $ordineTermini = data_get($this->dati, 'ordine.termini_consegna', 'N/A');
        
        $articoli = data_get($this->dati, 'articoli', []);
        $ordineTotale = data_get($this->dati, 'ordine.totale_ordine', 'N/A');
        
        $output = [];

        // Sezione Intestazione
        $output[] = [trans('exports.supplier', [], $this->locale), $fornitoreNome];
        $output[] = [trans('exports.supplier_address', [], $this->locale), $fornitoreIndirizzo];
        $output[] = [trans('exports.supplier_contacts', [], $this->locale), $fornitoreContatti];
        $output[] = [trans('exports.customer', [], $this->locale), $clienteNome];
        $output[] = [trans('exports.delivery_address', [], $this->locale), $clienteIndirizzo];
        $output[] = [trans('exports.order_date', [], $this->locale), $ordineData];
        $output[] = [trans('exports.order_number', [], $this->locale), $ordineNumero];
        $output[] = [trans('exports.delivery_terms', [], $this->locale), $ordineTermini];
        $output[] = []; // Riga vuota di spaziatura

        // Intestazioni Tabella Articoli
        $output[] = [
            trans('exports.item_code', [], $this->locale),
            trans('exports.description', [], $this->locale),
            trans('exports.quantity', [], $this->locale),
            trans('exports.unit_price', [], $this->locale),
            trans('exports.total', [], $this->locale)
        ];

        // Righe Articoli
        foreach ($articoli as $articolo) {
            $output[] = [
                data_get($articolo, 'codice', 'N/A'),
                data_get($articolo, 'descrizione', 'N/A'),
                data_get($articolo, 'quantita', 'N/A'),
                data_get($articolo, 'prezzo_unitario', 'N/A'),
                data_get($articolo, 'totale', 'N/A'),
            ];
        }

        // Riga Totale
        $output[] = ['', '', '', trans('exports.order_total', [], $this->locale), $ordineTotale];
        
        return $output;
    }

    public function title(): string
    {
        return 'Ordine';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();
        $headerInfoRows = 8;
        $articleHeaderRow = $headerInfoRows + 2;

        // Grassetto per le etichette di intestazione
        for ($i = 1; $i <= $headerInfoRows; $i++) {
            $sheet->getStyle('A' . $i)->getFont()->setBold(true);
        }
        
        // Grassetto per l'intestazione della tabella articoli
        $sheet->getStyle('A' . $articleHeaderRow . ':E' . $articleHeaderRow)->getFont()->setBold(true);
        
        // Grassetto per l'etichetta del totale
        $sheet->getStyle('D' . $lastRow)->getFont()->setBold(true);

        // Larghezza colonne
        $sheet->getColumnDimension('A')->setWidth(25);
        $sheet->getColumnDimension('B')->setWidth(45);
        $sheet->getColumnDimension('C')->setWidth(15);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(15);

        // Formato valuta per le colonne dei prezzi
        $firstArticleDataRow = $articleHeaderRow + 1;
        $lastPriceCell = $lastRow -1;
        if ($lastPriceCell >= $firstArticleDataRow) {
             $sheet->getStyle("D{$firstArticleDataRow}:E{$lastPriceCell}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
        }

        // Formato valuta per il totale finale
         $sheet->getStyle('E' . $lastRow)
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);

        return [];
    }
}
