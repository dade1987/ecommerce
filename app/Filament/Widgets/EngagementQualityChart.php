<?php

namespace App\Filament\Widgets;

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
        // Dati statici d’esempio: punteggio engagement AI su base oraria
        $labels = ['09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '21:00'];

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Punteggio engagement (0–10)',
                    'data' => [5.4, 6.2, 7.8, 8.5, 7.9, 6.8, 5.9],
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


