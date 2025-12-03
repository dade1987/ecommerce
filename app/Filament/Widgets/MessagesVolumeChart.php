<?php

namespace App\Filament\Widgets;

use App\Models\Quoter;
use Carbon\Carbon;
use Filament\Support\RawJs;
use Filament\Widgets\ChartWidget;

class MessagesVolumeChart extends ChartWidget
{
    protected static ?string $heading = 'Volume messaggi (ultimi giorni)';

    protected static ?string $maxHeight = '260px';

    protected static string $color = 'success';

    protected function getType(): string
    {
        // Grafico a barre verticali
        return 'bar';
    }

    /**
     * @return array<string, mixed>
     */
    protected function getData(): array
    {
        // Serie reale: numero di messaggi (solo reali, is_fake = false) negli ultimi 7 giorni
        $end = Carbon::now()->endOfDay();
        $start = $end->copy()->subDays(6)->startOfDay();

        $rawPerDay = Quoter::query()
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('is_fake', false)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')
            ->all();

        $labels = [];
        $values = [];

        $days = $start->diffInDays($end) + 1;

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->format('Y-m-d');

            $labels[] = $date->format('d/m');
            $values[] = $rawPerDay[$key] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Messaggi salvati in quoters',
                    'data' => $values,
                    // Colori vivaci per ogni barra
                    'backgroundColor' => [
                        'rgba(16, 185, 129, 0.85)',
                        'rgba(45, 212, 191, 0.85)',
                        'rgba(56, 189, 248, 0.85)',
                        'rgba(59, 130, 246, 0.9)',
                        'rgba(129, 140, 248, 0.9)',
                        'rgba(244, 114, 182, 0.9)',
                        'rgba(251, 146, 60, 0.9)',
                    ],
                    'borderRadius' => 999,
                    'borderSkipped' => false,
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
                    'ticks' => [
                        'stepSize' => 25,
                    ],
                ],
            ],
        ];
    }
}


