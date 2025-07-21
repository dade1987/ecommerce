<?php

namespace App\Filament\Pages;

use App\Services\Production\AdvancedSchedulingService;
use App\Services\Production\ProductionSchedulingService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
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

    public function mount(): void
    {
        $this->startDate = now()->startOfWeek()->format('Y-m-d');
        $this->endDate = now()->endOfWeek()->format('Y-m-d');
        $this->updateBottleneckData();
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

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Data Inizio')
                ->live()
                ->afterStateUpdated(fn () => $this->updateBottleneckData()),

            DatePicker::make('endDate')
                ->label('Data Fine')
                ->live()
                ->afterStateUpdated(fn () => $this->updateBottleneckData()),
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
        ];
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

    public function runSimpleScheduling()
    {
        $service = new ProductionSchedulingService();
        $service->scheduleProduction();
        Notification::make()
            ->title('Schedulazione semplice completata')
            ->success()
            ->send();
    }

    public function runAdvancedScheduling()
    {
        $service = new AdvancedSchedulingService();
        $result = $service->generateSchedule(); // This returns an array with logs

        // Count how many phases were actually scheduled from the log
        $scheduledPhasesCount = collect($result['log'] ?? [])->filter(fn($line) => str_starts_with($line, 'Scheduled'))->count();

        Notification::make()
            ->title('Schedulazione Avanzata Completata')
            ->success()
            ->body("Create o aggiornate {$scheduledPhasesCount} fasi di produzione.")
            ->send();

        $this->ganttChart = $this->generateGanttChart();
    }

    private function generateGanttChart(): ?string
    {
        $phases = \App\Models\ProductionPhase::whereNotNull('scheduled_start_time')
            ->whereNotNull('scheduled_end_time')
            ->with('productionOrder.bom', 'workstation') // Corrected: removed '.product'
            ->orderBy('scheduled_start_time')
            ->get();

        if ($phases->isEmpty()) {
            return null;
        }

        // Gantt chart settings
        $ganttWidth = 1200;
        $rowHeight = 40;
        $headerHeight = 50;
        $padding = 20;

        $startDate = $phases->min('scheduled_start_time');
        $endDate = $phases->max('scheduled_end_time');
        $totalDays = $startDate->diffInDays($endDate) + 1;
        $pixelsPerDay = $ganttWidth / $totalDays;

        $ganttHeight = ($phases->count() * $rowHeight) + $headerHeight + $padding;

        $svg = "<svg width=\"{$ganttWidth}\" height=\"{$ganttHeight}\" xmlns=\"http://www.w3.org/2000/svg\" style=\"font-family: sans-serif; background-color: #f9fafb;\">";

        // Header
        $currentDate = $startDate->copy();
        for ($i = 0; $i < $totalDays; $i++) {
            $x = $i * $pixelsPerDay;
            $svg .= "<line x1=\"{$x}\" y1=\"0\" x2=\"{$x}\" y2=\"{$ganttHeight}\" stroke=\"#e5e7eb\" />";
            $svg .= "<text x=\"" . ($x + 5) . "\" y=\"30\" font-size=\"12\" fill=\"#6b7280\">{$currentDate->format('d/m')}</text>";
            $currentDate->addDay();
        }

        // Rows and Bars
        foreach ($phases as $index => $phase) {
            $y = $headerHeight + ($index * $rowHeight);
            $startOffsetDays = $startDate->diffInDays($phase->scheduled_start_time);
            $durationDays = $phase->scheduled_start_time->diffInDays($phase->scheduled_end_time);
            if ($durationDays == 0) $durationDays = 0.5; // Min width for short tasks

            $barX = $startOffsetDays * $pixelsPerDay;
            $barWidth = $durationDays * $pixelsPerDay;

            // Row background
            $svg .= "<rect x=\"0\" y=\"{$y}\" width=\"{$ganttWidth}\" height=\"{$rowHeight}\" fill=\"" . ($index % 2 == 0 ? '#fff' : '#f9fafb') . "\" />";
            
            // Task bar
            $color = '#3b82f6'; // Blue
            $svg .= "<rect x=\"{$barX}\" y=\"" . ($y + 5) . "\" width=\"{$barWidth}\" height=\"" . ($rowHeight - 10) . "\" fill=\"{$color}\" rx=\"3\" />";
            
            // Task label
            $productName = $phase->productionOrder->bom->product_name ?? 'Prodotto non definito';
            $taskName = "Ordine {$phase->productionOrder->id}: {$productName} ({$phase->workstation->name})";
            $svg .= "<text x=\"" . ($barX + 5) . "\" y=\"" . ($y + $rowHeight / 2 + 5) . "\" font-size=\"12\" fill=\"#fff\">" . htmlspecialchars($taskName) . "</text>";
        }

        $svg .= "</svg>";

        return $svg;
    }
} 