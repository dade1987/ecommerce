<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIUsageWidget extends Widget
{
    protected static string $view = 'filament.widgets.openai-usage-widget';

    protected static ?int $sort = 2;

    public ?array $usageData = null;

    public ?string $error = null;

    public function mount(): void
    {
        $this->loadUsageData();
    }

    protected function loadUsageData(): void
    {
        try {
            $apiKey = config('services.openai.key');
            if (! $apiKey) {
                $this->error = 'API Key OpenAI non configurata';

                return;
            }

            // OpenAI non ha un endpoint pubblico per l'usage, quindi usiamo i dati disponibili
            // Possiamo calcolare un'approssimazione basata sulle chiamate effettuate
            $this->usageData = $this->calculateEstimatedUsage();
        } catch (\Throwable $e) {
            Log::error('OpenAIUsageWidget: Errore nel caricamento dati usage', [
                'error' => $e->getMessage(),
            ]);
            $this->error = 'Errore nel caricamento dei dati: '.$e->getMessage();
        }
    }

    protected function calculateEstimatedUsage(): array
    {
        // Calcola stime basate sui dati disponibili
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        // Conta le ricerche effettuate (ogni ricerca usa token)
        $searchesToday = \App\Models\WebsiteSearch::where('created_at', '>=', $today)->count();
        $searchesThisMonth = \App\Models\WebsiteSearch::where('created_at', '>=', $thisMonth)->count();

        // Stima: ogni ricerca usa ~500 token input + ~200 token output = ~700 token
        // gpt-4o-mini: $0.15/1M input, $0.60/1M output
        $avgTokensPerSearch = 700;
        $inputTokensToday = $searchesToday * ($avgTokensPerSearch * 0.7);
        $outputTokensToday = $searchesToday * ($avgTokensPerSearch * 0.3);
        $inputTokensMonth = $searchesThisMonth * ($avgTokensPerSearch * 0.7);
        $outputTokensMonth = $searchesThisMonth * ($avgTokensPerSearch * 0.3);

        $costToday = ($inputTokensToday / 1_000_000 * 0.15) + ($outputTokensToday / 1_000_000 * 0.60);
        $costMonth = ($inputTokensMonth / 1_000_000 * 0.15) + ($outputTokensMonth / 1_000_000 * 0.60);

        // Conta anche i messaggi chat (Quoter)
        $chatsToday = \App\Models\Quoter::where('created_at', '>=', $today)
            ->where('role', 'user')
            ->count();
        $chatsMonth = \App\Models\Quoter::where('created_at', '>=', $thisMonth)
            ->where('role', 'user')
            ->count();

        $chatTokensToday = $chatsToday * 1000; // Stima 1000 token per chat
        $chatTokensMonth = $chatsMonth * 1000;

        $chatCostToday = ($chatTokensToday / 1_000_000 * 0.15) + ($chatTokensToday / 1_000_000 * 0.60);
        $chatCostMonth = ($chatTokensMonth / 1_000_000 * 0.15) + ($chatTokensMonth / 1_000_000 * 0.60);

        return [
            'today' => [
                'searches' => $searchesToday,
                'chats' => $chatsToday,
                'estimated_tokens' => $inputTokensToday + $outputTokensToday + $chatTokensToday,
                'estimated_cost' => $costToday + $chatCostToday,
            ],
            'month' => [
                'searches' => $searchesThisMonth,
                'chats' => $chatsMonth,
                'estimated_tokens' => $inputTokensMonth + $outputTokensMonth + $chatTokensMonth,
                'estimated_cost' => $costMonth + $chatCostMonth,
            ],
        ];
    }
}
