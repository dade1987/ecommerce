<?php

namespace Tests\Unit\Services;

use App\Models\Article;
use App\Models\Page;
use App\Services\Seo\LlmsTxtGenerator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class LlmsTxtGeneratorTest extends TestCase
{
    public function test_generates_llms_txt_with_pages_and_articles(): void
    {
        // Arrange
        $pages = new Collection([
            new Page([
                'title' => 'Chi Siamo',
                'slug' => 'chi-siamo',
                'description' => 'Siamo un team che sviluppa software su misura.',
                'blocks' => [
                    [
                        'type' => 'text-block',
                        'data' => [
                            'text' => '<p>Testo della pagina <strong>chi siamo</strong>.</p>',
                        ],
                    ],
                ],
            ]),
        ]);

        $articles = new Collection([
            new Article([
                'title' => 'Guida SEO',
                'slug' => 'guida-seo',
                'summary' => 'Una guida breve alla SEO.',
                'content' => '<p>Contenuto articolo</p>',
            ]),
        ]);

        $generator = app(LlmsTxtGenerator::class);

        // Act
        $out = $generator->generate($pages, $articles, 'https://example.com');

        // Assert
        $this->assertStringContainsString('## Contenuti principali', $out);
        $this->assertStringContainsString('[Chi Siamo](https://example.com/chi-siamo)', $out);
        $this->assertStringContainsString('## Contenuti opzionali', $out);
        $this->assertStringContainsString('[Guida SEO](https://example.com/blog/guida-seo)', $out);
    }
}
