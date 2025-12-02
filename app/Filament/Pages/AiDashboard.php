<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ThreadResource;
use App\Models\Quoter;
use App\Models\Thread;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;

class AiDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-sparkles';

    protected static ?string $navigationGroup = 'AI & Assistant';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.ai-dashboard';

    /** @var array<string, mixed> */
    public array $threadStats = [];

    /** @var array<int, array<string, mixed>> */
    public array $messagesPerDay = [];

    /** @var array<int, array<string, mixed>> */
    public array $threadsPerTeam = [];

    /** @var \Illuminate\Support\Collection<int, Thread> */
    public $latestThreads;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public function mount(): void
    {
        $this->startDate = now()->subDays(6)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->loadDashboardData();
    }

    protected function getFormSchema(): array
    {
        return [
            DatePicker::make('startDate')
                ->label('Dal')
                ->displayFormat('d/m/Y')
                ->maxDate(fn () => $this->endDate ? Carbon::parse($this->endDate) : null)
                ->live()
                ->afterStateUpdated(fn () => $this->loadDashboardData()),
            DatePicker::make('endDate')
                ->label('Al')
                ->displayFormat('d/m/Y')
                ->minDate(fn () => $this->startDate ? Carbon::parse($this->startDate) : null)
                ->live()
                ->afterStateUpdated(fn () => $this->loadDashboardData()),
        ];
    }

    public function loadDashboardData(): void
    {
        $start = $this->startDate ? Carbon::parse($this->startDate)->startOfDay() : Carbon::now()->subDays(6)->startOfDay();
        $end = $this->endDate ? Carbon::parse($this->endDate)->endOfDay() : Carbon::now()->endOfDay();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        // Threads nel periodo selezionato (solo quelli registrati nella nuova tabella threads)
        $threadsInRange = Thread::whereBetween('created_at', [$start, $end])->get();
        $threadIds = $threadsInRange->pluck('thread_id');

        $totalThreads = $threadsInRange->count();

        // Messaggi conteggiati SOLO se appartengono a uno dei thread del periodo selezionato.
        // In questo modo ignoriamo vecchi record Quoter "orfani" o generati da sistemi legacy/token.
        $totalMessages = Quoter::whereIn('thread_id', $threadIds)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $this->threadStats = [
            'total_threads' => $totalThreads,
            'threads_last_7_days' => $totalThreads,
            'avg_messages_per_thread' => $totalThreads > 0 ? round($totalMessages / max($totalThreads, 1), 1) : 0,
            'unique_ips' => $threadsInRange
                ->whereNotNull('ip_address')
                ->unique('ip_address')
                ->count(),
        ];

        // Serie temporale: messaggi per ORA nel range selezionato
        // Usiamo un bucket per ogni ora (es. 2025-12-02 18:00:00) e poi riempiamo tutte le ore del range.
        $rawPerHour = Quoter::selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as h, COUNT(*) as c')
            ->whereIn('thread_id', $threadIds)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('h')
            ->orderBy('h')
            ->pluck('c', 'h')
            ->all();

        $messagesPerHour = [];
        $hours = $start->diffInHours($end) + 1;
        for ($i = 0; $i < $hours; $i++) {
            $dt = $start->copy()->addHours($i);
            $key = $dt->format('Y-m-d H:00:00');
            $messagesPerHour[] = [
                'label' => $dt->format('d/m H:i'),
                'count' => $rawPerHour[$key] ?? 0,
            ];
        }
        // Per retrocompatibilità col template Blade teniamo il nome $messagesPerDay
        $this->messagesPerDay = $messagesPerHour;

        // Distribuzione thread per team (prime 5 squadre) nel range selezionato
        $this->threadsPerTeam = $threadsInRange
            ->groupBy(fn (Thread $t) => $t->team_slug ?: 'n/d')
            ->map(fn ($group, $team) => [
                'team' => $team,
                'total' => $group->count(),
            ])
            ->sortByDesc('total')
            ->values()
            ->take(5)
            ->all();

        // Ultimi thread per tabella "Active Threads" nel range selezionato
        $this->latestThreads = Thread::withCount('messages')
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->each(function (Thread $thread): void {
                $thread->view_url = ThreadResource::getUrl('view', ['record' => $thread]);
            });
    }
}
