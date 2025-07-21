<?php

namespace Database\Seeders;

use App\Models\Bom;
use App\Models\ProductionLine;
use App\Models\ProductionOrder;
use App\Models\ProductionPhase;
use App\Models\Workstation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pulisce le disponibilità esistenti per garantire uno stato pulito
        \App\Models\WorkstationAvailability::truncate();

        // Use raw SQL for cleaning to be absolutely sure, in the correct order.
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('workstation_availabilities')->truncate();
        DB::table('production_phases')->truncate();
        DB::table('production_orders')->truncate();
        DB::table('workstations')->truncate();
        DB::table('production_lines')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Create Production Lines
        $lineaAssemblaggio = ProductionLine::create(['name' => 'Linea Assemblaggio', 'description' => 'Linea principale per assemblaggio componenti.']);
        $lineaFinitura = ProductionLine::create(['name' => 'Linea Finitura', 'description' => 'Linea per verniciatura e finiture finali.']);

        // 2. Create Workstations with realistic Digital Twin data
        $wsTaglio = Workstation::create([
            'production_line_id' => $lineaAssemblaggio->id, 'name' => 'Taglio Laser', 'capacity' => 8,
            'real_time_status' => 'running', 'current_speed' => 5, 'error_rate' => 2.5, 'wear_level' => rand(10, 30)
        ]);
        $wsSaldatura = Workstation::create([
            'production_line_id' => $lineaAssemblaggio->id, 'name' => 'Saldatura Robotizzata', 'capacity' => 8,
            'real_time_status' => 'idle', 'current_speed' => 0, 'error_rate' => 1.0, 'wear_level' => rand(5, 20)
        ]);
        $wsVerniciatura = Workstation::create([
            'production_line_id' => $lineaFinitura->id, 'name' => 'Cabina Verniciatura', 'capacity' => 8,
            'real_time_status' => 'running', 'current_speed' => 8, 'error_rate' => 5.0, 'wear_level' => rand(20, 50)
        ]);
        $wsControllo = Workstation::create([
            'production_line_id' => $lineaFinitura->id, 'name' => 'Controllo Qualità', 'capacity' => 8,
            'real_time_status' => 'faulted', 'current_speed' => 0, 'error_rate' => 0.5, 'wear_level' => rand(30, 60)
        ]);

        $workstations = [$wsTaglio, $wsSaldatura, $wsVerniciatura, $wsControllo];

        // 3. Define standard availability for each workstation (Monday to Friday)
        foreach ($workstations as $workstation) {
            for ($day = 1; $day <= 5; $day++) { // 1 = Monday, 5 = Friday
                \App\Models\WorkstationAvailability::create([
                    'workstation_id' => $workstation->id,
                    'day_of_week' => $day,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00',
                ]);
            }
        }
        
        // 4. Create Production Orders
        $boms = Bom::factory(10)->create();
        if ($boms->isEmpty()) {
            $this->command->info('Nessuna Distinta Base (BOM) trovata. Salto la creazione degli Ordini di Produzione.');
            return;
        }

        for ($i = 0; $i < 20; $i++) {
            $order = ProductionOrder::create([
                'production_line_id' => rand(0, 1) ? $lineaAssemblaggio->id : $lineaFinitura->id,
                'customer' => 'Cliente ' . ($i + 1),
                'order_date' => now()->subDays(rand(1, 30)),
                'status' => 'in_attesa',
                'priority' => rand(0, 5),
                'bom_id' => $boms->random()->id,
                'notes' => 'Note per ordine ' . ($i + 1),
            ]);

            // 5. Create Production Phases for each order
            $phases_data = [
                ['name' => 'Preparazione Materiali', 'duration' => rand(30, 60)],
                ['name' => 'Lavorazione Principale', 'duration' => rand(120, 240)],
                ['name' => 'Assemblaggio Componenti', 'duration' => rand(60, 180)],
                ['name' => 'Finitura Superficiale', 'duration' => rand(45, 90)],
                ['name' => 'Test Funzionale', 'duration' => rand(30, 60)],
            ];
            
            shuffle($phases_data);
            $order_phases = array_slice($phases_data, 0, rand(3, 5));
            
            foreach($order_phases as $phase_data) {
                ProductionPhase::create([
                    'production_order_id' => $order->id,
                    'workstation_id' => $workstations[array_rand($workstations)]->id,
                    'name' => $phase_data['name'],
                    'estimated_duration' => $phase_data['duration'],
                    'setup_time' => rand(5, 15), // Aggiunge un tempo di setup casuale
                ]);
            }
        }

        // 6. Aggiungi fasi di manutenzione programmata
        foreach ($workstations as $workstation) {
            $duration = rand(60, 120);
            $startTime = now()->addDays(rand(2, 5))->hour(10);
            ProductionPhase::create([
                'production_order_id' => ProductionOrder::inRandomOrder()->first()->id, // Assegnato a un ordine a caso
                'workstation_id' => $workstation->id,
                'name' => 'Manutenzione Programmata',
                'estimated_duration' => $duration,
                'is_maintenance' => true,
                'scheduled_start_time' => $startTime,
                'scheduled_end_time' => $startTime->copy()->addMinutes($duration),
            ]);
        }
    }
}
