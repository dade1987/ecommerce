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

        $usersToCreate = [
            [
                'email' => 'amministrazione@formificiostf.it',
                'name' => 'Amministrazione',
                'password' => 'Amm.STF.2024!',
            ],
            [
                'email' => 'andrea.tripodi@formificiostf.it',
                'name' => 'Andrea Tripodi',
                'password' => 'Andrea.Tripodi.2024!',
            ],
            [
                'email' => 'robertogatto99@gmail.com',
                'name' => 'Roberto Gatto',
                'password' => 'Roberto.Gatto.2024!',
            ],
        ];

        foreach ($usersToCreate as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                ]
            );

            // Assign the 'tripodi' role to the user
            $user->assignRole($tripodiRole);
        }

        $this->command->info('3 utenti Tripodi creati e ruolo assegnato con successo!');
    }
} 