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
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Http;

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
     * Costo GPT (OpenAI) in USD per gli ultimi 30 giorni, letto direttamente dall'endpoint
     * /v1/organization/costs che restituisce gli importi già calcolati.
     */
    public ?float $openAiCreditUsd = null;

    /**
     * Eventuale messaggio di errore per il widget GPT.
     */
    public ?string $openAiCreditError = null;

    /**
     * Credito residuo HeyGen (in "crediti" API) calcolato a partire dall'endpoint
     * ufficiale /v2/user/remaining_quota.
     *
     * La documentazione HeyGen specifica che per ottenere i crediti va diviso
     * il campo remaining_quota per 60.
     */
    public ?string $heygenCreditDisplay = null;

    public ?string $heygenCreditError = null;

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
        $this->loadCreditData();
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
                ->where('content', 'like', '%'.$search.'%')
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
        if (! $this->selectedThreadId) {
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

    /**
     * Carica i dati di costo/credito per GPT (OpenAI) e HeyGen.
     *
     * Per GPT usiamo l'endpoint costs ufficiale
     * https://api.openai.com/v1/organization/costs
     * che restituisce direttamente i costi in USD già calcolati per periodo.
     *
     * Per HeyGen usiamo l'endpoint ufficiale /v2/user/remaining_quota
     * che restituisce il remaining_quota convertito in crediti (quota / 60).
     */
    protected function loadCreditData(): void
    {
        $this->loadOpenAiCredit();
        $this->loadHeygenCredit();
    }

    protected function loadOpenAiCredit(): void
    {
        $this->openAiCreditUsd = null;
        $this->openAiCreditError = null;

        $apiKey = config('openapi.key');

        if (empty($apiKey) || $apiKey === 'invalid key') {
            $this->openAiCreditError = 'API key OpenAI non configurata.';

            return;
        }

        try {
            // Endpoint costs OpenAI: restituisce direttamente i costi in USD
            // GET /v1/organization/costs?start_time=UNIX_SECONDS&end_time=UNIX_SECONDS
            $startTime = now()->subDays(30)->startOfDay()->timestamp;
            $endTime = now()->endOfDay()->timestamp;

            $totalCost = 0.0;
            $nextPage = null;

            // Gestiamo la paginazione: facciamo più chiamate se necessario
            do {
                $params = [
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                    'bucket_width' => '1d',
                    'limit' => 180, // massimo consentito per ottenere tutti i bucket
                ];

                if ($nextPage) {
                    $params['page'] = $nextPage;
                }

                $response = Http::withHeaders([
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ])->get('https://api.openai.com/v1/organization/costs', $params);

                if (! $response->successful()) {
                    throw new \RuntimeException('HTTP '.$response->status().' '.$response->body());
                }

                /** @var array<string,mixed> $json */
                $json = $response->json();

                // Somma i costi da tutti i bucket
                if (isset($json['data']) && is_array($json['data'])) {
                    foreach ($json['data'] as $bucket) {
                        if (isset($bucket['results']) && is_array($bucket['results'])) {
                            foreach ($bucket['results'] as $result) {
                                if (isset($result['amount']['value'])) {
                                    $totalCost += (float) $result['amount']['value'];
                                }
                            }
                        }
                    }
                }

                // Controlla se c'è una pagina successiva
                $nextPage = $json['next_page'] ?? null;
                $hasMore = $json['has_more'] ?? false;
            } while ($hasMore && $nextPage);

            // Mostra il costo totale degli ultimi 30 giorni
            $this->openAiCreditUsd = $totalCost > 0 ? $totalCost : null;
        } catch (\Throwable $e) {
            $message = (string) $e->getMessage();

            if (str_contains($message, 'HTTP')) {
                $this->openAiCreditError = 'Errore nel recupero costs OpenAI: '.$message;
            } else {
                $this->openAiCreditError = 'Errore nel recupero costs OpenAI.';
            }
        }
    }

    protected function loadHeygenCredit(): void
    {
        $this->heygenCreditDisplay = null;
        $this->heygenCreditError = null;

        $apiKey = config('services.heygen.api_key');
        $serverUrl = rtrim(config('services.heygen.server_url', 'https://api.heygen.com'), '/');

        if (empty($apiKey)) {
            $this->heygenCreditError = 'API key HeyGen non configurata.';

            return;
        }

        try {
            $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'accept' => 'application/json',
            ])->get($serverUrl.'/v2/user/remaining_quota');

            if (! $response->successful()) {
                throw new \RuntimeException('HTTP '.$response->status());
            }

            /** @var array<string,mixed> $json */
            $json = $response->json();

            // Gestione robusta in caso di wrapper tipo { data: { remaining_quota: ... } }
            $remainingQuota = 0.0;
            if (isset($json['remaining_quota'])) {
                $remainingQuota = (float) $json['remaining_quota'];
            } elseif (isset($json['data']['remaining_quota'])) {
                $remainingQuota = (float) $json['data']['remaining_quota'];
            } elseif (isset($json['result']['remaining_quota'])) {
                $remainingQuota = (float) $json['result']['remaining_quota'];
            }

            // La doc HeyGen indica: credits = remaining_quota / 60
            $remainingCredits = $remainingQuota > 0 ? ($remainingQuota / 60.0) : null;

            if ($remainingCredits === null) {
                $this->heygenCreditDisplay = 'n/d';
            } else {
                $this->heygenCreditDisplay = number_format($remainingCredits, 0).' crediti';
            }
        } catch (\Throwable $e) {
            $this->heygenCreditError = 'Errore nel recupero del credito HeyGen.';
        }
    }
}
