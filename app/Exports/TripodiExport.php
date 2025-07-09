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
    public function collection(): Collection
    {
        $rows = new Collection();

        if (empty($this->data)) {
            return $rows;
        }

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
        // Return empty array since we'll handle headers within the data
        return [];
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

        // First, add rows for base data (vertical layout)
        $flattenedBaseData = $this->flattenData($baseData);
        foreach ($flattenedBaseData as $key => $value) {
            $readableKey = $this->makeKeyReadable($key);
            $rows[] = [$readableKey, $value];
        }

        // Then, add header row for repeating data if it exists
        if ($repeatingArray !== null && !empty($repeatingArray)) {
            // Add empty row as separator
            $rows[] = ['', ''];
            
            // Get headers from first item of repeating array
            $firstItem = $repeatingArray[0];
            $flattenedFirstItem = $this->flattenData($firstItem);
            $headers = array_keys($flattenedFirstItem);
            $readableHeaders = array_map([$this, 'makeKeyReadable'], $headers);
            
            // Add header row for articles
            $rows[] = $readableHeaders;
            
            // Add data rows for each item in repeating array
            foreach ($repeatingArray as $item) {
                $flattenedItem = $this->flattenData($item);
                $values = array_values($flattenedItem);
                $rows[] = $values;
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
     * Flattens nested arrays using dot notation, but removes redundant prefixes
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
                    // Skip arrays that should become separate rows
                    continue;
                }
            } else {
                $result[$newKey] = $value;
            }
        }
        
        return $result;
    }

    /**
     * Convert snake_case and dot notation keys to readable format, removing redundant prefixes
     */
    private function makeKeyReadable(string $key): string
    {
        // Remove redundant prefixes (e.g., "ordine.numero_ordine" becomes "numero_ordine")
        $parts = explode('.', $key);
        
        // If we have nested keys, check for redundancy
        if (count($parts) > 1) {
            $lastPart = end($parts);
            $secondLastPart = count($parts) > 1 ? $parts[count($parts) - 2] : '';
            
            // If the last part contains the second-to-last part, use only the last part
            if (strpos($lastPart, $secondLastPart) !== false || 
                strpos($secondLastPart, $lastPart) !== false) {
                $key = $lastPart;
            } else {
                // Otherwise, use the last two parts
                $key = implode('_', array_slice($parts, -2));
            }
        }
        
        // Convert snake_case to Title Case
        $readableKey = str_replace('_', ' ', $key);
        return ucwords($readableKey);
    }
} 