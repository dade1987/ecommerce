<?php

namespace App\Imports;

use App\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CustomerImport implements ToModel, WithHeadingRow
{
    /**
     * Map CSV rows to the Customer model.
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Validate the CSV data before inserting
        Validator::make($row, [
            'ragione_sociale' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'telefono' => 'nullable|string|max:255',
            'sito_web' => 'nullable|url|max:255',
        ])->validate();

        return new Customer([
            'name' => $row['ragione_sociale'] ?? null,
            'email' => $row['email'] ?? null,
            'phone' => $row['telefono'] ?? null,
            'website' => $row['sito_web'] ?? null,
            'status' => 'not_contacted', // Default status
        ]);
    }
}
