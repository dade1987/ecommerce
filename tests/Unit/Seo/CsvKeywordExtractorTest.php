<?php

namespace Tests\Unit\Seo;

use App\Services\Seo\CsvKeywordExtractor;
use function Safe\file_put_contents;
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
