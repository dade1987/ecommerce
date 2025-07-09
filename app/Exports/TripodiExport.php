<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TripodiExport implements FromCollection, WithHeadings
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return new Collection($this->data);
    }

    /**
    * @return array
    */
    public function headings(): array
    {
        if (empty($this->data)) {
            return [];
        }

        // Assumiamo che il primo elemento nell'array di dati contenga tutte le chiavi per le intestazioni
        return array_keys($this->data[0]);
    }
} 