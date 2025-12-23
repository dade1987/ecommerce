<?php

namespace App\Services\Seo;

use Modules\WebScraper\Services\AiAnalyzerService;
use function Safe\json_decode;

class KeywordRelevanceService
{
    public function __construct(
        protected PageSourceWithScriptsFetcher $pageFetcher,
        protected AiAnalyzerService $aiAnalyzer,
        protected MenuItemUrlResolver $urlResolver,
    ) {
    }

    /**
     * @param array<int, string> $keywords
     * @return array<int, array{keyword:string, score:int, reason:string}>
     */
    public function suggest(string $targetHref, array $keywords): array
    {
        $keywords = array_values(array_filter(array_map('strval', $keywords), fn ($k) => trim($k) !== ''));
        $keywords = array_values(array_unique($keywords));

        if (empty($keywords)) {
            return [];
        }

        $url = $this->urlResolver->resolveHref($targetHref);
        $scraped = $this->pageFetcher->fetch($targetHref);

        if (isset($scraped['error'])) {
            throw new \RuntimeException('Errore scraping URL: '.$scraped['error']);
        }

        $apiKeyConfig = config('openapi.key');
        $apiKey = is_string($apiKeyConfig) ? $apiKeyConfig : '';
        if (trim($apiKey) === '') {
            // Fallback senza LLM: match testuale sul contenuto pagina
            return $this->fallbackSuggest($scraped, $keywords);
        }

        $keywordList = implode("\n", array_map(fn ($k) => '- '.$k, array_slice($keywords, 0, 2000)));

        $query = <<<PROMPT
Hai una lista di keyword (inclusa sotto) e il contenuto della pagina di destinazione.
Seleziona le keyword che sono:
- direttamente correlate alla pagina, oppure
- indirettamente correlate ma “agganciabili” con un posizionamento marketing credibile (es: se la pagina parla di interprete virtuale, puoi intercettare anche chi cerca “traduttore arabo italiano” spiegando che l’interprete virtuale può essere usato per quel caso d’uso).
Scarta SOLO le keyword totalmente fuori contesto o ingannevoli.

IMPORTANTE (modalità ampia):
- Includi anche keyword con match parziale/adiacente (long-tail, lingue, sinonimi, casi d’uso vicini) se l’articolo può colmare il gap senza promettere cose false.
- Usa lo score per riflettere il tipo:
  - 80-100: match diretto
  - 50-79: correlata ma non perfetta
  - 30-49: “adattabile/agganciabile” (va bene includerla se è sensata)
- Se trovi almeno 20 keyword sensate, restituiscine almeno 20 (non limitarti solo alle perfette).

Vincoli:
- Puoi scegliere SOLO keyword presenti nella lista.
- Evita duplicati e keyword quasi identiche (mantieni la più naturale).
- Restituisci SOLO JSON valido, senza testo aggiuntivo.

Output richiesto:
{
  "relevant": [
    {"keyword": "...", "score": 0-100, "reason": "..."}
  ]
}

LISTA KEYWORD:
{$keywordList}
PROMPT;

        $analysis = $this->aiAnalyzer->extractCustomInfo($scraped, $query);
        if (isset($analysis['error'])) {
            return $this->fallbackSuggest($scraped, $keywords);
        }

        $analysisText = $analysis['analysis'] ?? '';
        $analysisText = is_string($analysisText) ? $analysisText : '';
        $parsed = $this->tryParseJson($analysisText);
        if (! is_array($parsed) || ! isset($parsed['relevant']) || ! is_array($parsed['relevant'])) {
            return $this->fallbackSuggest($scraped, $keywords);
        }

        $allowed = array_flip($keywords);
        $out = [];

        foreach ($parsed['relevant'] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $kw = trim((string) ($row['keyword'] ?? ''));
            if ($kw === '' || ! isset($allowed[$kw])) {
                continue;
            }
            $score = (int) ($row['score'] ?? 0);
            $score = max(0, min(100, $score));
            $reason = trim((string) ($row['reason'] ?? ''));
            if ($reason === '') {
                $reason = 'Correlata al contenuto della pagina.';
            }

            $out[] = [
                'keyword' => $kw,
                'score' => $score,
                'reason' => $reason,
            ];
        }

        // Ordina per score desc
        usort($out, static fn (array $a, array $b): int => (int) $b['score'] <=> (int) $a['score']);

        // Dedup safety
        $seen = [];
        $deduped = [];
        foreach ($out as $row) {
            if (isset($seen[$row['keyword']])) {
                continue;
            }
            $seen[$row['keyword']] = true;
            $deduped[] = $row;
        }

        return $deduped;
    }

    /**
     * @param array<string, mixed> $scraped
     * @param array<int, string> $keywords
     * @return array<int, array{keyword:string, score:int, reason:string}>
     */
    private function fallbackSuggest(array $scraped, array $keywords): array
    {
        $metadata = $scraped['metadata'] ?? [];
        $metadata = is_array($metadata) ? $metadata : [];
        $content = $scraped['content'] ?? [];
        $content = is_array($content) ? $content : [];

        $titleRaw = $metadata['title'] ?? '';
        $descRaw = $metadata['description'] ?? '';
        $fullRaw = $content['full'] ?? '';
        $mainRaw = $content['main'] ?? '';

        $title = strtolower(is_string($titleRaw) ? $titleRaw : '');
        $desc = strtolower(is_string($descRaw) ? $descRaw : '');
        $text = strtolower(is_string($fullRaw) ? $fullRaw : (is_string($mainRaw) ? $mainRaw : ''));

        $out = [];
        foreach ($keywords as $kw) {
            $needle = strtolower(trim($kw));
            if ($needle === '') {
                continue;
            }

            $score = 0;
            if ($title !== '' && str_contains($title, $needle)) {
                $score = 95;
            } elseif ($desc !== '' && str_contains($desc, $needle)) {
                $score = 80;
            } elseif ($text !== '' && str_contains($text, $needle)) {
                $score = 65;
            }

            if ($score > 0) {
                $out[] = [
                    'keyword' => $kw,
                    'score' => $score,
                    'reason' => 'Match sul contenuto della pagina (fallback senza LLM).',
                ];
            }
        }

        usort($out, static fn (array $a, array $b): int => (int) $b['score'] <=> (int) $a['score']);

        return $out;
    }

    /**
     * @return array<mixed>|null
     */
    private function tryParseJson(string $text): ?array
    {
        $text = trim($text);
        if ($text === '') {
            return null;
        }

        try {
            $decoded = json_decode($text, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        } catch (\Throwable $e) {
            // continua con fallback sotto
        }

        // Tenta di estrarre un oggetto JSON contenuto nel testo
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        $slice = substr($text, $start, $end - $start + 1);
        try {
            $decoded = json_decode($slice, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }
}
