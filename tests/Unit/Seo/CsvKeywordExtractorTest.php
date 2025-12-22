<?php

namespace Tests\Unit\Seo;

use App\Services\Seo\CsvKeywordExtractor;
use function Safe\file_put_contents;
use function Safe\mb_convert_encoding;
use function Safe\tempnam;
use Tests\TestCase;

class CsvKeywordExtractorTest extends TestCase
{
    public function test_extracts_keywords_from_simple_single_column_csv(): void
    {
        // Arrange
        $tmp = tempnam(sys_get_temp_dir(), 'kwcsv_');
        file_put_contents($tmp, "keyword\nscarpe running\nscarpe trekking\n");

        // Act
        $keywords = app(CsvKeywordExtractor::class)->extractFromPath($tmp);

        // Assert
        $this->assertContains('scarpe running', $keywords);
        $this->assertContains('scarpe trekking', $keywords);
    }

    public function test_extracts_keywords_from_semicolon_csv_with_header(): void
    {
        // Arrange
        $tmp = tempnam(sys_get_temp_dir(), 'kwcsv_');
        file_put_contents($tmp, "id;keyphrase;volume\n1;scarpe uomo;1000\n2;scarpe donna;1200\n");

        // Act
        $keywords = app(CsvKeywordExtractor::class)->extractFromPath($tmp);

        // Assert
        $this->assertSame(['scarpe uomo', 'scarpe donna'], $keywords);
    }

    public function test_extracts_keywords_from_utf16_keyword_planner_like_export(): void
    {
        // Arrange
        $tmp = tempnam(sys_get_temp_dir(), 'kwcsv_');

        $utf8 = "Keyword Stats 2025-10-22 at 09_07_05\n"
            ."1 dicembre 2024 - 30 novembre 2025\n"
            ."Keyword\tCurrency\tAvg. monthly searches\n"
            ."interprete\tEUR\t5000\n"
            ."traduttore\tEUR\t1200\n";

        // Add UTF-16LE BOM + convert
        $converted = mb_convert_encoding($utf8, 'UTF-16LE', 'UTF-8');
        $converted = is_array($converted) ? implode('', $converted) : $converted;
        $utf16le = "\xFF\xFE".$converted;
        file_put_contents($tmp, $utf16le);

        // Act
        $keywords = app(CsvKeywordExtractor::class)->extractFromPath($tmp);

        // Assert
        $this->assertContains('interprete', $keywords);
        $this->assertContains('traduttore', $keywords);
        $this->assertNotContains('Keyword', $keywords);
        $this->assertNotContains('Currency', $keywords);
    }

    public function test_splits_multiple_keywords_inside_one_cell(): void
    {
        // Arrange
        $tmp = tempnam(sys_get_temp_dir(), 'kwcsv_');
        file_put_contents($tmp, "keyword\n\"scarpe, sneakers\";\"boots\"\n");

        // Act
        $keywords = app(CsvKeywordExtractor::class)->extractFromPath($tmp);

        // Assert
        $this->assertContains('scarpe', $keywords);
        $this->assertContains('sneakers', $keywords);
        $this->assertContains('boots', $keywords);
    }
}
