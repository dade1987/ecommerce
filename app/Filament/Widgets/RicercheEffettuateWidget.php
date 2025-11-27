<?php

namespace App\Filament\Widgets;

use App\Models\WebsiteSearch;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class RicercheEffettuateWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        $todayCount = WebsiteSearch::whereDate('created_at', $today)->count();
        $weekCount = WebsiteSearch::where('created_at', '>=', $thisWeek)->count();
        $monthCount = WebsiteSearch::where('created_at', '>=', $thisMonth)->count();
        $totalCount = WebsiteSearch::count();

        $cacheHitRate = WebsiteSearch::where('from_cache', true)->count();
        $totalWithCache = WebsiteSearch::whereNotNull('from_cache')->count();
        $cacheRate = $totalWithCache > 0 ? round(($cacheHitRate / $totalWithCache) * 100, 1) : 0;

        return [
            Stat::make('Ricerche Oggi', $todayCount)
                ->description('Ricerche effettuate oggi')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('primary'),
            Stat::make('Ricerche Questa Settimana', $weekCount)
                ->description('Ricerche effettuate questa settimana')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),
            Stat::make('Ricerche Questo Mese', $monthCount)
                ->description('Ricerche effettuate questo mese')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info'),
            Stat::make('Totale Ricerche', $totalCount)
                ->description("Tasso cache: {$cacheRate}%")
                ->descriptionIcon('heroicon-m-server')
                ->color('warning'),
        ];
    }
}
