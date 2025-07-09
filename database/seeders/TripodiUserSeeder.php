<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TripodiUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create the 'tripodi' role if it doesn't exist
        $tripodiRole = Role::firstOrCreate(['name' => 'tripodi', 'guard_name' => 'web']);

        // Create the user for 'tripodi' role
        $user = User::firstOrCreate(
            ['email' => 'tripodi@example.com'],
            [
                'name' => 'Utente Tripodi',
                'password' => Hash::make('password'), // You should change this to a strong password in production
            ]
        );

        // Assign the 'tripodi' role to the user
        $user->assignRole($tripodiRole);

        $this->command->info('Utente Tripodi creato e ruolo assegnato con successo!');
    }
} 