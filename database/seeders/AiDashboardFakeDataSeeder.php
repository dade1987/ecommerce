<?php

namespace Database\Seeders;

use App\Models\Quoter;
use App\Models\Thread;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AiDashboardFakeDataSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Generiamo dati finti ma verosimili per 14 giorni,
        // con un numero variabile di thread e messaggi per giorno.
        $days = 14;

        for ($i = $days; $i >= 1; $i--) {
            $day = Carbon::now()->subDays($i - 1)->startOfDay();

            $threadsForDay = random_int(2, 8);

            for ($t = 0; $t < $threadsForDay; $t++) {
                $createdAt = (clone $day)->addHours(random_int(8, 21))->addMinutes(random_int(0, 59));

                /** @var \App\Models\Thread $thread */
                $thread = Thread::factory()->create([
                    'is_fake' => true,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);

                $messagesCount = random_int(4, 14);
                $role = 'user';

                for ($m = 0; $m < $messagesCount; $m++) {
                    $messageCreatedAt = (clone $createdAt)->addMinutes($m * random_int(1, 4));

                    Quoter::factory()->create([
                        'thread_id' => $thread->thread_id,
                        'role' => $role,
                        'is_fake' => true,
                        'created_at' => $messageCreatedAt,
                        'updated_at' => $messageCreatedAt,
                    ]);

                    // Alterna fra utente e chatbot per rendere la conversazione realistica.
                    $role = $role === 'user' ? 'chatbot' : 'user';
                }
            }
        }
    }
}


