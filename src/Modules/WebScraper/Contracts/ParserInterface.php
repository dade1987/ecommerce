<?php

namespace Modules\WebScraper\Contracts;

interface ParserInterface
{
    /**
     * Parse HTML content and extract structured data
     *
     * @param string $html Raw HTML content
     * @return array Parsed data
     */
    public function parse(string $html): array;

    /**
     * Extract main content from HTML
     *
     * @param string $html
     * @return string
     */
    public function extractMainContent(string $html): string;

    /**
     * Extract metadata from HTML
     *
     * @param string $html
     * @return array
     */
    public function extractMetadata(string $html): array;

    /**
     * Remove boilerplate content (header, footer, sidebar)
     *
     * @param string $html
     * @return string
     */
    public function removeBoilerplate(string $html): string;
}