<?php

namespace App\Filament\Pages;

use Filament\Panel;
use Filament\Pages\Page;

class Welcome extends Page
{
    public static ?string $label = 'Custom Navigation Label';

    public static ?string $slug = 'test';

    public static ?string $title = 'Pagina Custom Test';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.welcome';


    public function panel(Panel $panel): Panel
    {
        return $panel->navigation(false);
    }
}
