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
        $rows = new Collection();

        if (empty($this->data)) {
            return $rows;
        }

        // Determina se il dato di input è un array di elementi (molte righe) o un singolo elemento (una riga).
        // Se le chiavi sono numeriche e consecutive, assumiamo che sia un array di righe.
        $isCollectionOfItems = array_keys($this->data) === range(0, count($this->data) - 1);

        if ($isCollectionOfItems) {
            foreach ($this->data as $item) {
                if (is_array($item)) {
                    $rows->push($this->flattenArrayRecursive($item));
                }
            }
        } else {
            // Se non è una collezione di elementi, trattiamo l'intero array come una singola riga
            $rows->push($this->flattenArrayRecursive($this->data));
        }

        return $rows;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Ottengo la prima riga dalla collezione per derivare le intestazioni
        $firstRow = $this->collection()->first();

        if (empty($firstRow)) {
            return [];
        }

        return array_keys($firstRow);
    }

    /**
     * Recursively flattens an array, prepending keys with a prefix.
     *
     * @param array $array The array to flatten.
     * @param string $prefix The prefix to use for keys.
     * @return array The flattened array.
     */
    private function flattenArrayRecursive(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
            if (is_array($value) && !empty($value)) {
                // If it's an associative array, recurse
                if (array_keys($value) !== range(0, count($value) - 1)) { // Check if associative
                    $result = array_merge($result, $this->flattenArrayRecursive($value, $newKey));
                } else {
                    // If it's a numeric array (list), flatten each item with its index
                    foreach ($value as $idx => $item) {
                        if (is_array($item)) {
                            $result = array_merge($result, $this->flattenArrayRecursive($item, $newKey . '.' . $idx));
                        } else {
                            $result[$newKey . '.' . $idx] = $item; // For simple lists like [1,2,3]
                        }
                    }
                }
            } else {
                $result[$newKey] = $value;
            }
        }
        return $result;
    }
} 