<?php

namespace App\Services\Seo;

use function Safe\fclose;
use function Safe\file_get_contents;
use function Safe\mb_convert_encoding;
use function Safe\preg_match;
use function Safe\preg_replace;
use function Safe\preg_split;

class CsvKeywordExtractor
{
    /**
     * Estrae una lista di keyword da un CSV con formati variabili.
     *
     * @return array<int, string>
     */
    public function extractFromPath(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new \InvalidArgumentException('File CSV non leggibile: '.$path);
        }

        $raw = file_get_contents($path);
        $text = $this->normalizeToUtf8($raw);

        $lines = preg_split("/\r\n|\n|\r/", $text);
        $lines = array_values(array_filter(array_map('trim', $lines), fn ($l) => $l !== ''));

        if (count($lines) === 0) {
            return [];
        }

        $delimiter = $this->guessDelimiterFromLines($lines);

        // Parse lines -> rows
        $rows = [];
        foreach ($lines as $line) {
            $row = str_getcsv($line, $delimiter);
            // normalizza righe vuote
            if ($row === [null]) {
                continue;
            }
            $rows[] = $row;
        }

        if (count($rows) === 0) {
            return [];
        }

        // Trova la riga header: alcuni export hanno preambolo ("Keyword Stats ...")
        $headerRowIndex = $this->findHeaderRowIndex($rows);
        $header = array_map(fn ($v) => is_string($v) ? $this->cleanCell($v) : '', $rows[$headerRowIndex] ?? []);
        $hasHeader = $this->looksLikeHeader($header);

        $dataRows = $hasHeader ? array_slice($rows, $headerRowIndex + 1) : $rows;
        $keywordColumnIndexes = $hasHeader ? $this->findKeywordColumns($header) : [];

        if ($hasHeader && empty($keywordColumnIndexes)) {
            // fallback: prima colonna
            $keywordColumnIndexes = [0];
        }

        if (! $hasHeader) {
            // fallback: prima colonna
            $keywordColumnIndexes = [0];
        }

        $keywords = [];

        foreach ($dataRows as $row) {
            foreach ($keywordColumnIndexes as $idx) {
                $cell = $row[$idx] ?? null;
                if (! is_string($cell)) {
                    continue;
                }

                $cell = $this->cleanCell($cell);
                if ($this->isGarbageKeyword($cell)) {
                    continue;
                }

                foreach ($this->splitKeywords($cell) as $kw) {
                    $kw = $this->normalizeKeyword($kw);
                    if ($kw !== '') {
                        $keywords[] = $kw;
                    }
                }
            }
        }

        $keywords = array_values(array_unique($keywords));

        // Limite hard per evitare prompt ingestibili
        return array_slice($keywords, 0, 2000);
    }

    /**
     * Prova a capire il delimitatore valutando più righe, non solo la prima.
     *
     * @param array<int, string> $lines
     */
    private function guessDelimiterFromLines(array $lines): string
    {
        $candidates = [',', ';', "\t", '|'];
        $best = ',';
        $bestCount = -1;

        foreach (array_slice($lines, 0, 20) as $line) {
            foreach ($candidates as $c) {
                $count = substr_count($line, $c);
                if ($count > $bestCount) {
                    $bestCount = $count;
                    $best = $c;
                }
            }
        }

        return $best;
    }

    /**
     * @param array<int, string> $header
     */
    private function looksLikeHeader(array $header): bool
    {
        // euristica semplice: presenza di parole tipiche di header
        $joined = strtolower(implode(' ', $header));

        return preg_match('/\b(keyword|keyphrase|query|parola|chiave|term|kw)\b/i', $joined) === 1;
    }

    /**
     * @param array<int, array<int, mixed>> $rows
     */
    private function findHeaderRowIndex(array $rows): int
    {
        $maxScan = min(20, count($rows));

        for ($i = 0; $i < $maxScan; $i++) {
            $row = $rows[$i] ?? [];
            if (! is_array($row)) {
                continue;
            }
            $cells = array_map(fn ($v) => is_string($v) ? $this->cleanCell($v) : '', $row);
            if ($this->looksLikeHeader($cells)) {
                return $i;
            }
        }

        return 0;
    }

    /**
     * @param array<int, string> $header
     * @return array<int, int>
     */
    private function findKeywordColumns(array $header): array
    {
        $indexes = [];

        foreach ($header as $i => $name) {
            $n = strtolower($name);
            if (preg_match('/\b(keyword|keyphrase|query|parola|chiave|term|kw)\b/i', $n) === 1) {
                $indexes[] = (int) $i;
            }
        }

        return $indexes;
    }

    /**
     * @return array<int, string>
     */
    private function splitKeywords(string $cell): array
    {
        $cell = trim($cell);
        if ($cell === '') {
            return [];
        }

        // Alcuni CSV mettono più keyword nella stessa cella
        $parts = preg_split("/[;\n\r\t]+/", $cell);
        $out = [];

        foreach ($parts as $p) {
            $p = trim((string) $p);
            if ($p === '') {
                continue;
            }

            // se dentro ci sono virgole, spesso sono liste
            $sub = preg_split('/\s*,\s*/', $p);
            foreach ($sub as $s) {
                $s = trim((string) $s);
                if ($s !== '') {
                    $out[] = $s;
                }
            }
        }

        return $out;
    }

    private function normalizeKeyword(string $kw): string
    {
        $kw = trim($kw);
        $kw = preg_replace('/\s+/', ' ', $kw);

        // ripulisci virgolette esterne
        $kw = trim($kw, " \t\n\r\0\x0B\"'");

        return trim($kw);
    }

    private function cleanCell(string $cell): string
    {
        // Rimuove eventuali null bytes (UTF-16 mal interpretato) + normalizza spazi
        $cell = str_replace("\0", '', $cell);
        $cell = trim($cell);
        $cell = preg_replace('/\s+/', ' ', $cell);

        return trim($cell);
    }

    private function isGarbageKeyword(string $cell): bool
    {
        $c = strtolower(trim($cell));
        if ($c === '') {
            return true;
        }

        // header singole più comuni
        if (in_array($c, ['keyword', 'keyphrase', 'query', 'parola chiave', 'term', 'kw', 'currency'], true)) {
            return true;
        }

        // Intestazioni e metriche tipiche di export (es. Keyword Planner)
        if (preg_match('/\b(keyword stats|currency|avg\.|monthly searches|competition|top of page bid|ad impression share|organic)\b/i', $c) === 1) {
            return true;
        }
        if (preg_match('/^searches:\s*/i', $c) === 1) {
            return true;
        }
        if (preg_match('/^\d+(\.\d+)?%?$/', $c) === 1) {
            return true;
        }

        return false;
    }

    private function normalizeToUtf8(string $raw): string
    {
        // Strip UTF-8 BOM
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3);
        }

        // UTF-16 LE/BE BOM
        if (str_starts_with($raw, "\xFF\xFE")) {
            $raw = substr($raw, 2);
            try {
                $utf8 = mb_convert_encoding($raw, 'UTF-8', 'UTF-16LE');

                return is_array($utf8) ? implode('', $utf8) : $utf8;
            } catch (\Throwable $e) {
                return '';
            }
        }
        if (str_starts_with($raw, "\xFE\xFF")) {
            $raw = substr($raw, 2);
            try {
                $utf8 = mb_convert_encoding($raw, 'UTF-8', 'UTF-16BE');

                return is_array($utf8) ? implode('', $utf8) : $utf8;
            } catch (\Throwable $e) {
                return '';
            }
        }

        // Heuristic: many null bytes => likely UTF-16LE without BOM
        if (substr_count($raw, "\0") > 0) {
            try {
                $utf8 = mb_convert_encoding($raw, 'UTF-8', 'UTF-16LE');
                $utf8 = is_array($utf8) ? implode('', $utf8) : $utf8;
                if (trim($utf8) !== '') {
                    return $utf8;
                }
            } catch (\Throwable $e) {
                // fallback sotto
            }
            $raw = str_replace("\0", '', $raw);
        }

        // Assume already UTF-8-ish
        return $raw;
    }
}
