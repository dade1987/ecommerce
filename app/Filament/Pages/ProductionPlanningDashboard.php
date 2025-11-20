<?php

namespace App\Filament\Pages;

use App\Models\Bom;
use App\Models\Workstation;
use App\Services\Forecasting\DemandForecastingService;
use App\Services\Production\AdvancedSchedulingService;
use App\Services\Production\GanttChartService;
use App\Services\Production\OeeService;
use App\Services\Production\ProductionSchedulingService;
use App\Services\Production\SimulationService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Http;

class ProductionPlanningDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    public static function getNavigationLabel(): string
    {
        return __('filament-production.Dashboard di Pianificazione');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-production.Produzione');
    }

    protected static string $view = 'filament.pages.production-planning-dashboard';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public array $bottleneckData = [];

    public ?string $ganttChart = null;

    public ?string $simulationGantt = null;

    public ?array $demandForecast = null;

    public array $oeeData = [];

    public function mount(): void
    {
        $this->startDate = now()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->endOfWeek()->format('Y-m-d');
        $this->updateDashboardData();
    }

    public function updateDashboardData(): void
    {
        $this->updateBottleneckData();
        $this->updateOeeData();
    }

    public function updateBottleneckData(): void
    {
        if ($this->startDate && $this->endDate) {
            $service = app(ProductionSchedulingService::class);
            $this->bottleneckData = $service->predictBottlenecks(
                Carbon::parse($this->startDate),
                Carbon::parse($this->endDate)
            )->toArray();
        }
    }

    public function updateOeeData(): void
    {
        $oeeService = new OeeService();
        $workstations = Workstation::all();
        $totalOee = 0;
        $startDate = Carbon::parse($this->startDate);
        $endDate = Carbon::parse($this->endDate);

        if ($workstations->isEmpty()) {
            $this->oeeData = ['avg_oee' => 0];

            return;
        }

        foreach ($workstations as $workstation) {
            $totalOee += $oeeService->calculateForWorkstation($workstation, $startDate, $endDate)['oee'];
        }

        $this->oeeData = [
            'avg_oee' => round(($totalOee / $workstations->count()) * 100, 2),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label(__('filament-production.Data Inizio'))
                ->live()
                ->afterStateUpdated(fn () => $this->updateDashboardData()),

            DatePicker::make('endDate')
                ->label(__('filament-production.Data Fine'))
                ->live()
                ->afterStateUpdated(fn () => $this->updateDashboardData()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('balanceLines')
                ->label(__('filament-production.Bilancia Carichi Linee'))
                ->action('balanceProductionLines'),
            Action::make('scheduleProduction')
                ->label(__('filament-production.Avvia Schedulazione (Semplice)'))
                ->action('runSimpleScheduling'),
            Action::make('generateGantt')
                ->label(__('filament-production.Genera Schedulazione Avanzata (Gantt)'))
                ->action('runAdvancedScheduling'),
            Action::make('generateDemandForecast')
                ->label(__('filament-production.Genera Forecast Domanda'))
                ->action('runDemandForecast'),
            Action::make('whatIfSimulation')
                ->label(__('filament-production.Simulazione What-If'))
                ->action(function (array $data): void {
                    $simulationService = new SimulationService();
                    $result = $simulationService->runWhatIfSimulation($data);

                    $ganttService = new GanttChartService();
                    $this->simulationGantt = $ganttService->generateForData($result['scheduled_phases']);

                    Notification::make()
                        ->title(__('filament-production.Simulazione What-If Completata'))
                        ->success()
                        ->body(__('filament-production.Il Gantt simulato è stato generato qui sotto.'))
                        ->send();
                })
                ->form([
                    TextInput::make('customer')->label(__('filament-production.Cliente Ipotetico'))->required(),
                    Select::make('bom_id')
                        ->label(__('filament-production.Distinta Base'))
                        ->options(Bom::pluck('internal_code', 'id'))
                        ->searchable()
                        ->required(),
                    TextInput::make('priority')->label(__('filament-production.Priorità'))->numeric()->required()->default(3),
                    Textarea::make('notes')->label(__('filament-production.Note Aggiuntive')),
                ]),
        ];
    }

    public function runDemandForecast(): void
    {
        $forecastingService = new DemandForecastingService();
        $result = $forecastingService->predictDemand();
        $this->demandForecast = $result['forecast'];

        Notification::make()
            ->title(__('filament-production.Previsione della Domanda Calcolata'))
            ->success()
            ->body(__('filament-production.Il volume previsto per il prossimo mese è di :volume unità.', ['volume' => $this->demandForecast['next_month_volume']]))
            ->send();
    }

    public function balanceProductionLines()
    {
        $service = new ProductionSchedulingService();
        $service->balanceProductionLines();
        $this->updateBottleneckData();
        Notification::make()
            ->title(__('filament-production.Linee di produzione bilanciate con successo'))
            ->success()
            ->send();
    }

    public function runAdvancedScheduling()
    {
        $scheduler = new AdvancedSchedulingService();
        $result = $scheduler->generateSchedule();

        $scheduledPhasesCount = count($result['scheduled_phases_data']);

        Notification::make()
            ->title(__('filament-production.Schedulazione Avanzata Completata'))
            ->success()
            ->body(__('filament-production.Schedulate con successo :count fasi di produzione e manutenzione.', ['count' => $scheduledPhasesCount]))
            ->send();

        $ganttService = new GanttChartService();
        $this->ganttChart = $ganttService->generateForData($result['scheduled_phases_data']);
        $this->simulationGantt = null; // Resetta il gantt di simulazione
    }

    public function runSimpleScheduling(): void
    {
        $service = new ProductionSchedulingService();
        $service->scheduleProduction();
        $this->updateBottleneckData();
        Notification::make()
            ->title(__('filament-production.Schedulazione semplice completata'))
            ->success()
            ->send();
    }
}
