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
        $articolo = $this->ordine['dettagli_articoli'][0];
        $quantita = $articolo['quantita_per_taglia'];
        $taglie = array_keys($quantita);
        $quantita_valori = array_values($quantita);

        return [
            ['Nome Cliente', '', $cliente['nome']],
            ['Destinazione', '', $dest['indirizzo']],
            ['Data Ordine', '', $this->ordine['data_ordine']],
            ['Data Consegna', '', $this->ordine['data_consegna']],
            ['Numero Ordine', '', str_replace('/', ' ', $this->ordine['numero_ordine'])],
            ['Persona Contatto', '', 'YSS - '.$dest['referente']],
            [],

            ['Matricola', '', $articolo['matricola'] ?? ''],
            ['Marcatura', '', $articolo['descrizione'] ?? ''],
            ['Taglie', '', '', '', '', '', ...$taglie],
            ['Quantità', '', '', '', '', '', ...$quantita_valori],
            ['Calzata', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', $articolo['calzata'] ?? ''],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Grassetto dalla riga 1 alla 6
        for ($i = 1; $i <= 6; $i++) {
            $sheet->getStyle("A{$i}:C{$i}")->getFont()->setBold(true);
        }

        return [];
    }

    public function title(): string
    {
        return 'Ordine';
    }
}
