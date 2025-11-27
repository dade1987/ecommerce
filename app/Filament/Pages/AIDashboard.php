<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ChatLogsWidget;
use App\Filament\Widgets\OpenAIUsageWidget;
use App\Filament\Widgets\RicercheEffettuateWidget;
use App\Filament\Widgets\TopQueriesWidget;
use App\Filament\Widgets\TopTeamsWidget;
use Filament\Pages\Page;

class AIDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static string $view = 'filament.pages.a-i-dashboard';

    public static function getNavigationLabel(): string
    {
        return 'Dashboard AI';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'AI & Chatbot';
    }

    protected static ?int $navigationSort = 1;

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RicercheEffettuateWidget::class,
            OpenAIUsageWidget::class,
            //TopTeamsWidget::class,
            TopQueriesWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            ChatLogsWidget::class,
        ];
    }
}
