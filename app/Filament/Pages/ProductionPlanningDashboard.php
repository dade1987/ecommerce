<?php

namespace App\Filament\Pages;

use App\Services\Forecasting\DemandForecastingService;
use App\Services\Production\AdvancedSchedulingService;
use App\Services\Production\OeeService;
use App\Services\Production\ProductionSchedulingService;
use App\Services\Production\SimulationService;
use App\Services\Production\GanttChartService;
use App\Models\Bom;
use App\Models\Workstation;
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
    protected static ?string $navigationLabel = 'Dashboard di Pianificazione';
    protected static ?string $navigationGroup = 'Produzione';
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

        if ($workstations->isEmpty()) {
            $this->oeeData = ['avg_oee' => 0];
            return;
        }

        foreach ($workstations as $workstation) {
            $totalOee += $oeeService->calculateForWorkstation($workstation)['oee'];
        }

        $this->oeeData = [
            'avg_oee' => round(($totalOee / $workstations->count()) * 100, 2),
        ];
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Data Inizio')
                ->live()
                ->afterStateUpdated(fn () => $this->updateDashboardData()),

            DatePicker::make('endDate')
                ->label('Data Fine')
                ->live()
                ->afterStateUpdated(fn () => $this->updateDashboardData()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('balanceLines')
                ->label('Bilancia Carichi Linee')
                ->action('balanceProductionLines'),
            Action::make('scheduleProduction')
                ->label('Avvia Schedulazione (Semplice)')
                ->action('runSimpleScheduling'),
            Action::make('generateGantt')
                ->label('Genera Schedulazione Avanzata (Gantt)')
                ->action('runAdvancedScheduling'),
            Action::make('generateDemandForecast')
                ->label('Genera Forecast Domanda')
                ->action('runDemandForecast'),
            Action::make('whatIfSimulation')
                ->label('Simulazione "What-If"')
                ->action(function (array $data): void {
                    $simulationService = new SimulationService();
                    $result = $simulationService->runWhatIfSimulation($data);
                    
                    $ganttService = new GanttChartService();
                    $this->simulationGantt = $ganttService->generateForData($result['scheduled_phases']);

                    Notification::make()
                        ->title('Simulazione "What-If" Completata')
                        ->success()
                        ->body('Il Gantt simulato è stato generato qui sotto.')
                        ->send();
                })
                ->form([
                    TextInput::make('customer')->label('Cliente Ipotetico')->required(),
                    Select::make('bom_id')
                        ->label('Distinta Base')
                        ->options(Bom::all()->pluck('product_name', 'id'))
                        ->searchable()
                        ->required(),
                    TextInput::make('priority')->label('Priorità')->numeric()->required()->default(3),
                    Textarea::make('notes')->label('Note Aggiuntive'),
                ]),
        ];
    }

    public function runDemandForecast(): void
    {
        $forecastingService = new DemandForecastingService();
        $result = $forecastingService->predictDemand();
        $this->demandForecast = $result['forecast'];

        Notification::make()
            ->title('Previsione della Domanda Calcolata')
            ->success()
            ->body("Il volume previsto per il prossimo mese è di {$this->demandForecast['next_month_volume']} unità.")
            ->send();
    }

    public function balanceProductionLines()
    {
        $service = new ProductionSchedulingService();
        $service->balanceProductionLines();
        $this->updateBottleneckData();
        Notification::make()
            ->title('Linee di produzione bilanciate con successo')
            ->success()
            ->send();
    }

    public function runAdvancedScheduling()
    {
        $scheduler = new AdvancedSchedulingService();
        $result = $scheduler->generateSchedule();
        
        $scheduledPhasesCount = count($result['scheduled_phases_data']);

        Notification::make()
            ->title('Schedulazione Avanzata Completata')
            ->success()
            ->body("Schedulate con successo {$scheduledPhasesCount} fasi di produzione e manutenzione.")
            ->send();
        
        $ganttService = new GanttChartService();
        $this->ganttChart = $ganttService->generateForData($result['scheduled_phases_data']);
        $this->simulationGantt = null; // Resetta il gantt di simulazione
    }
} 