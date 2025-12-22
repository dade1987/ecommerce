<?php

namespace App\Services\Seo;

use function Safe\fclose;
use function Safe\fgetcsv;
use function Safe\fopen;
use function Safe\preg_match;
use function Safe\preg_replace;
use function Safe\preg_split;
use function Safe\rewind;

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

        $handle = fopen($path, 'rb');

        // Leggi una riga per stimare delimitatore
        $firstLine = fgets($handle);
        rewind($handle);

        $delimiter = $this->guessDelimiter($firstLine ?: '');

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            // normalizza righe vuote
            if ($row === [null]) {
                continue;
            }
            $rows[] = $row;
        }
        fclose($handle);

        if (count($rows) === 0) {
            return [];
        }

        $header = array_map(fn ($v) => is_string($v) ? trim($v) : '', $rows[0]);
        $hasHeader = $this->looksLikeHeader($header);

        $dataRows = $hasHeader ? array_slice($rows, 1) : $rows;
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

    private function guessDelimiter(string $line): string
    {
        $candidates = [',', ';', "\t", '|'];
        $best = ',';
        $bestCount = -1;

        foreach ($candidates as $c) {
            $count = substr_count($line, $c);
            if ($count > $bestCount) {
                $bestCount = $count;
                $best = $c;
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
}
