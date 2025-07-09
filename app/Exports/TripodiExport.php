<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TripodiExport implements FromCollection, WithHeadings
{
    protected array $data;
    protected array $allHeaders = [];

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

        $this->allHeaders = [];
        $processedRows = $this->processDataToRows($this->data);

        foreach ($processedRows as $row) {
            $rows->push($row);
        }

        return $rows;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Ensure collection() is called first to populate headers
        $this->collection();
        return array_keys($this->allHeaders);
    }

    /**
     * Processes the input data to generate rows for export
     */
    private function processDataToRows(array $data): array
    {
        $rows = [];
        
        // Find the first repeating array (numeric array with objects)
        $repeatingArray = null;
        $repeatingKey = null;
        $baseData = [];

        foreach ($data as $key => $value) {
            if (is_array($value) && $this->isNumericArrayOfObjects($value)) {
                $repeatingArray = $value;
                $repeatingKey = $key;
            } else {
                $baseData[$key] = $value;
            }
        }

        // Flatten the base data (non-repeating parts)
        $flattenedBaseData = $this->flattenData($baseData);

        if ($repeatingArray !== null) {
            // Create a row for each item in the repeating array
            foreach ($repeatingArray as $item) {
                $flattenedItem = $this->flattenData($item);
                $combinedRow = array_merge($flattenedBaseData, $flattenedItem);
                
                // Convert keys to readable format
                $readableRow = $this->makeKeysReadable($combinedRow);
                $rows[] = $readableRow;
                
                // Collect all headers
                foreach ($readableRow as $header => $value) {
                    $this->allHeaders[$header] = true;
                }
            }
        } else {
            // No repeating array found, create single row
            $readableRow = $this->makeKeysReadable($flattenedBaseData);
            $rows[] = $readableRow;
            
            foreach ($readableRow as $header => $value) {
                $this->allHeaders[$header] = true;
            }
        }

        return $rows;
    }

    /**
     * Check if an array is a numeric array containing objects (arrays)
     */
    private function isNumericArrayOfObjects(array $array): bool
    {
        if (empty($array)) {
            return false;
        }
        
        // Check if keys are numeric and consecutive
        $keys = array_keys($array);
        if ($keys !== range(0, count($array) - 1)) {
            return false;
        }
        
        // Check if first element is an array (object)
        return is_array($array[0]);
    }

    /**
     * Flattens nested arrays using dot notation
     */
    private function flattenData(array $data, string $prefix = ''): array
    {
        $result = [];
        
        foreach ($data as $key => $value) {
            $newKey = $prefix === '' ? $key : $prefix . '.' . $key;
            
            if (is_array($value) && !empty($value)) {
                // If it's an associative array, recurse
                if (!$this->isNumericArrayOfObjects($value)) {
                    $result = array_merge($result, $this->flattenData($value, $newKey));
                } else {
                    // For arrays that should become rows, we don't flatten them here
                    // This prevents creating multiple columns for array elements
                    $result[$newKey] = json_encode($value); // Store as JSON for now
                }
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Convert snake_case and dot notation keys to readable format
     */
    private function makeKeysReadable(array $data): array
    {
        $readable = [];
        
        foreach ($data as $key => $value) {
            // Convert snake_case to Title Case and replace dots with spaces
            $readableKey = str_replace(['_', '.'], ' ', $key);
            $readableKey = ucwords($readableKey);
            
            $readable[$readableKey] = $value;
        }
        
        return $readable;
    }
} 