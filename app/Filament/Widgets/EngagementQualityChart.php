<?php

namespace App\Filament\Widgets;

use App\Models\Quoter;
use App\Models\Thread;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class EngagementQualityChart extends ChartWidget
{
    protected static ?string $heading = 'Qualità engagement conversazioni';

    protected static ?string $maxHeight = '260px';

    protected static string $color = 'warning';

    protected function getType(): string
    {
        // Grafico a linea morbida
        return 'line';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        // Dato reale derivato: media messaggi per thread (solo reali, is_fake = false) negli ultimi 7 giorni,
        // normalizzata in un range 0–10 per rappresentare un "punteggio" di engagement.
        $end = Carbon::now()->endOfDay();
        $start = $end->copy()->subDays(6)->startOfDay();

        $labels = [];
        $values = [];

        $days = $start->diffInDays($end) + 1;

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);

            $threadsCount = Thread::query()
                ->where('is_fake', false)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->count();

            $messagesCount = Quoter::query()
                ->where('is_fake', false)
                ->whereDate('created_at', $date->format('Y-m-d'))
                ->count();

            $avg = $threadsCount > 0 ? $messagesCount / $threadsCount : 0.0;

            // Normalizziamo / clamp in 0–10 per restare coerenti con il titolo.
            $score = min(10, round($avg, 1));

            $labels[] = $date->format('d/m');
            $values[] = $score;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Punteggio engagement (0–10)',
                    'data' => $values,
                    'fill' => true,
                    'tension' => 0.45,
                    'backgroundColor' => 'rgba(251, 191, 36, 0.18)',
                    'borderColor' => 'rgba(234, 179, 8, 0.95)',
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed> | RawJs | null
     */
    protected function getOptions(): array | RawJs | null
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'tooltip' => [
                    'enabled' => true,
                    'backgroundColor' => 'rgba(15, 23, 42, 0.95)',
                    'titleFont' => ['weight' => '600'],
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
                'y' => [
                    'beginAtZero' => true,
                    'suggestedMax' => 10,
                    'ticks' => [
                        'stepSize' => 2,
                    ],
                ],
            ],
        ];
    }
}


