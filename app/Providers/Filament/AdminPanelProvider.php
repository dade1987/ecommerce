<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Tenancy\EditTeamProfile;
use App\Filament\Pages\Tenancy\RegisterTeam;
use App\Models\Team;
use Awcodes\Curator\CuratorPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Z3d0X\FilamentFabricator\FilamentFabricatorPlugin;
use App\Filament\Plugins\RichEditor\ColorPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->id('admin')
            ->path('admin')
            ->login()

            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])->plugins(

                [
                    ColorPlugin::make(),
                    FilamentFabricatorPlugin::make(),
                    FilamentShieldPlugin::make(),
                    CuratorPlugin::make(),

                    FilamentFullCalendarPlugin::make()
                        ->selectable(true)
                        ->editable(true)
                        ->config([
                            'initialView' => 'timeGridWeek',
                            'headerToolbar' => [
                                'left' => 'prev,next',
                                'center' => 'title',
                                //'right' => 'dayGridMonth,timeGridWeek,timeGridDay',
                                'right' => 'timeGridWeek,timeGridDay',
                            ],
                            'selectHelper' => true,
                            /*'slotDuration' => '00:30:00',
                            'snapDuration' => '00:30:00',
                            'slotLabelInterval' => '00:30:00',*/
                            'slotDuration' => '01:00:00',
                            'snapDuration' => '01:00:00',
                            'slotLabelInterval' => '01:00:00',

                            //'slotMinTime' => '08:00:00',
                            'slotMinTime' => '09:00:00',

                            //'slotMaxTime' => '20:00:00',
                            'slotMaxTime' => '18:00:00',

                            'slotLabelFormat' => [
                                'hour' => '2-digit',
                                'minute' => '2-digit',
                                'omitZeroMinute' => false,
                                'meridiem' => 'short',
                            ],
                            // nascondo Sabato e Domenica
                            'hiddenDays' => [0, 6],
                            // rimuovi la prenotazione del giorno intero
                            'allDaySlot' => false,
                        ]),
                ]
            )
            //->tenant(Team::class, ownershipRelationship: 'team', slugAttribute: 'slug')
            ->tenantRegistration(page: RegisterTeam::class)
            ->tenantProfile(EditTeamProfile::class)
            ->navigation(
                function (Panel $panel): array {
                    /** @var \App\Models\User $user */
                    $user = auth()->user();

                    if (! $user) {
                        return [];
                    }

                    if ($user->hasRole('super_admin')) {
                        // Get all resources registered with Filament
                        $resources = $panel->getResources();

                        $navigationItems = [
                            \Filament\Navigation\NavigationItem::make('Dashboard')
                                ->icon('heroicon-o-home')
                                ->url(fn (): string => Pages\Dashboard::getUrl())
                                ->isActiveWhen(fn (): bool => request()->routeIs('filament.pages.dashboard')),
                        ];

                        foreach ($resources as $resource) {
                            if ($resource::canViewAny($user)) {
                                $navigationItems[] = \Filament\Navigation\NavigationItem::make($resource::getNavigationLabel())
                                    ->icon($resource::getNavigationIcon())
                                    ->url(fn (): string => $resource::getUrl('index'))
                                    ->isActiveWhen(fn (): bool => request()->routeIs($resource::getRouteBaseName() . '.*'));
                            }
                        }
                        return $navigationItems;

                    }

                    if ($user->hasRole('tripodi')) {
                        return [
                            \Filament\Navigation\NavigationGroup::make('Gestione Extractor')
                                ->items([
                                    \Filament\Navigation\NavigationItem::make('Extractors')
                                        ->icon('heroicon-o-rectangle-stack')
                                        ->url(fn (): string => \App\Filament\Resources\ExtractorResource::getUrl('index'))
                                        ->isActiveWhen(fn (): bool => request()->fullUrlIs(\App\Filament\Resources\ExtractorResource::getUrl('index')))
                                        ->visible(true),
                                ]),
                        ];
                    }

                    return [];
                }
            );
    }
}
