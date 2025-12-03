<?php

namespace App\Filament\Pages;

use App\Filament\Resources\ThreadResource;
use App\Models\Quoter;
use App\Models\Thread;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
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

    public function getHeading(): string | Htmlable
    {
        // Restituiamo stringa vuota così l'header della page non viene renderizzato
        return '';
    }

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

    public ?string $selectedThreadId = null;

    public ?string $threadSearch = null;

    /**
     * Se true, la tabella in basso mostra i dati fake generati dal seeder.
     * Se false, mostra solo i dati reali.
     */
    public bool $showFake = false;

    public function mount(): void
    {
        $this->startDate = now()->subDays(6)->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->loadDashboardData();
    }
    
    public function searchThreads(): void
    {
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

        /** @var Builder<Thread> $threadQuery */
        $threadQuery = Thread::query()
            ->where('is_fake', false)
            ->whereBetween('created_at', [$start, $end]);

        $totalThreads = $threadQuery->count();

        // Ottieni i thread_id dei thread nel periodo
        $threadIds = $threadQuery->pluck('thread_id');

        // Conta solo i messaggi creati nel periodo che appartengono ai thread nel periodo
        $totalMessages = Quoter::query()
            ->where('is_fake', false)
            ->whereIn('thread_id', $threadIds)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        // Calcola la media corretta: solo messaggi dei thread nel periodo creati nel periodo
        $avgMessagesPerThread = $totalThreads > 0 ? round($totalMessages / $totalThreads, 1) : 0;

        $this->threadStats = [
            'total_threads' => $totalThreads,
            'threads_last_7_days' => $totalThreads,
            'avg_messages_per_thread' => $avgMessagesPerThread,
            'unique_ips' => $threadQuery->whereNotNull('ip_address')->distinct('ip_address')->count('ip_address'),
        ];

        // Serie temporale: messaggi per giorno nel range selezionato
        $rawPerDay = Quoter::query()
            ->selectRaw('DATE(created_at) as d, COUNT(*) as c')
            ->where('is_fake', false)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('d')
            ->orderBy('d')
            ->pluck('c', 'd')
            ->all();

        $messagesPerDay = [];
        $days = $start->diffInDays($end) + 1;
        for ($i = 0; $i < $days; $i++) {
            $d = $start->copy()->addDays($i)->format('Y-m-d');
            $messagesPerDay[] = [
                'date' => $d,
                'count' => $rawPerDay[$d] ?? 0,
            ];
        }
        $this->messagesPerDay = $messagesPerDay;

        // Distribuzione thread per team (prime 5 squadre) nel range selezionato
        $this->threadsPerTeam = Thread::query()
            ->selectRaw('COALESCE(team_slug, "n/d") as team, COUNT(*) as total')
            ->where('is_fake', false)
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('team')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'team' => $row->team,
                'total' => $row->total,
            ])
            ->all();

        // Ultimi thread per tabella "Active Threads" nel range selezionato
        /** @var Builder<Thread> $latestThreadsQuery */
        $latestThreadsQuery = Thread::query()
            ->withCount(['messages' => function (Builder $query): void {
                $query->where('is_fake', $this->showFake);
            }])
            ->where('is_fake', $this->showFake)
            ->whereBetween('created_at', [$start, $end]);

        if (! empty($this->threadSearch)) {
            $search = $this->threadSearch;

            // Filtra i thread che hanno almeno un messaggio il cui contenuto contiene il testo cercato
            $matchingThreadIds = Quoter::query()
                ->where('is_fake', $this->showFake)
                ->where('content', 'like', '%' . $search . '%')
                ->pluck('thread_id');

            $latestThreadsQuery->whereIn('thread_id', $matchingThreadIds);
        }

        $this->latestThreads = $latestThreadsQuery
            ->orderByDesc('created_at')
            ->limit(6)
            ->get()
            ->each(function (Thread $thread): void {
                $thread->view_url = ThreadResource::getUrl('view', ['record' => $thread]);
            });
    }

    public function openConversationModal(string $threadId): void
    {
        $this->selectedThreadId = $threadId;
        $this->dispatch('open-modal', id: 'conversation-modal');
    }

    public function getConversationMessagesProperty()
    {
        if (!$this->selectedThreadId) {
            return collect();
        }

        return Quoter::query()
            ->where('thread_id', $this->selectedThreadId)
            ->where('is_fake', $this->showFake)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function toggleFakeDataset(): void
    {
        $this->showFake = ! $this->showFake;
        $this->loadDashboardData();
    }
}
