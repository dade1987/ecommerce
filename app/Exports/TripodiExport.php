<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TripodiExport implements FromCollection, WithHeadings
{
    protected array $data;
    protected array $generatedHeaders = [];

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection(): Collection
    {
        $rows = new Collection();

        if (empty($this->data)) {
            return $rows;
        }

        $this->generatedHeaders = [];

        // Check if the top-level data itself is a collection of items (e.g., array of records)
        $isTopLevelCollection = array_keys($this->data) === range(0, count($this->data) - 1) && !empty($this->data) && is_array($this->data[array_key_first($this->data)]);

        if ($isTopLevelCollection) {
            foreach ($this->data as $item) {
                // For each top-level item, try to find a deeper repeating array
                $outputRowsFromItem = $this->processSingleItemForRepeatingArrays($item, '');
                foreach ($outputRowsFromItem as $row) {
                    $rows->push($row);
                }
            }
        } else {
            // If it's a single top-level object, process it
            $outputRowsFromItem = $this->processSingleItemForRepeatingArrays($this->data, '');
            foreach ($outputRowsFromItem as $row) {
                $rows->push($row);
            }
        }

        return $rows;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Ensure collection() is called first to populate generatedHeaders
        $this->collection(); // This will populate $this->generatedHeaders
        return array_keys($this->generatedHeaders);
    }

    /**
     * Processes a single item (which could be the entire top-level data or an item from a top-level collection)
     * to find a repeating array and generate rows.
     *
     * @param array $itemData The data for the current item.
     * @param string $prefix The current prefix for keys.
     * @return array An array of flattened rows.
     */
    private function processSingleItemForRepeatingArrays(array $itemData, string $prefix = ''): array
    {
        $outputRows = [];
        $repeatingArrayKey = null;
        $nonRepeatingParts = [];

        // Identify the first repeating array within this item
        foreach ($itemData as $key => $value) {
            if (is_array($value) && array_keys($value) === range(0, count($value) - 1) && !empty($value) && is_array($value[array_key_first($value)])) {
                $repeatingArrayKey = $key;
                break;
            } else {
                $nonRepeatingParts[$key] = $value;
            }
        }

        $flattenedNonRepeatingParts = $this->flattenArrayRecursive($nonRepeatingParts, $prefix);

        if ($repeatingArrayKey !== null && !empty($itemData[$repeatingArrayKey])) {
            foreach ($itemData[$repeatingArrayKey] as $index => $subItem) {
                $subItemPrefix = ($prefix ? $prefix . '.' : '') . $repeatingArrayKey . '.' . $index;
                $flattenedSubItem = $this->flattenArrayRecursive($subItem, $subItemPrefix);

                $combinedRow = array_merge($flattenedNonRepeatingParts, $flattenedSubItem);
                $outputRows[] = $combinedRow;

                // Collect headers
                foreach ($combinedRow as $header => $val) {
                    $this->generatedHeaders[$header] = true;
                }
            }
        } else {
            // No repeating array found at this level, so this item forms a single row.
            $outputRows[] = $flattenedNonRepeatingParts;

            // Collect headers
            foreach ($flattenedNonRepeatingParts as $header => $val) {
                $this->generatedHeaders[$header] = true;
            }
        }
        return $outputRows;
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