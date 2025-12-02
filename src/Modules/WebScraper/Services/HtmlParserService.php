<?php

namespace Modules\WebScraper\Services;

use DOMDocument;
use DOMXPath;
use Modules\WebScraper\Contracts\ParserInterface;
use Illuminate\Support\Facades\Log;

class HtmlParserService implements ParserInterface
{
    /**
     * Parse HTML content and extract structured data
     */
    public function parse(string $html): array
    {
        $dom = $this->loadHtml($html);
        if (!$dom) {
            return ['error' => 'Failed to parse HTML'];
        }

        return [
            'metadata' => $this->extractMetadata($html),
            'main_content' => $this->extractMainContent($html),
            'headings' => $this->extractHeadings($dom),
            'links' => $this->extractLinks($dom),
            'images' => $this->extractImages($dom),
            'structured_content' => $this->extractStructuredContent($dom),
        ];
    }

    /**
     * Extract main content from HTML
     */
    public function extractMainContent(string $html): string
    {
        $dom = $this->loadHtml($html);
        if (!$dom) {
            return '';
        }

        // Remove boilerplate first
        $cleaned = $this->removeBoilerplate($html);
        $dom = $this->loadHtml($cleaned);

        $xpath = new DOMXPath($dom);

        // Try to find main content containers
        $mainSelectors = [
            '//main',
            '//article',
            '//div[@id="content"]',
            '//div[@class*="content"]',
            '//div[@id="main"]',
            '//div[@class*="main"]',
        ];

        foreach ($mainSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes && $nodes->length > 0) {
                $content = $this->extractTextFromNode($nodes->item(0));
                if (strlen($content) > 200) { // Minimum content threshold
                    return $this->cleanText($content);
                }
            }
        }

        // Fallback: extract from body
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            return $this->cleanText($this->extractTextFromNode($body));
        }

        return '';
    }

    /**
     * Extract metadata from HTML
     */
    public function extractMetadata(string $html): array
    {
        $dom = $this->loadHtml($html);
        if (!$dom) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $metadata = [];

        // Title
        $title = $dom->getElementsByTagName('title');
        if ($title->length > 0) {
            $metadata['title'] = trim($title->item(0)->textContent);
        }

        // Meta tags
        $metaTags = [
            'description' => '//meta[@name="description"]/@content',
            'keywords' => '//meta[@name="keywords"]/@content',
            'author' => '//meta[@name="author"]/@content',
            'og:title' => '//meta[@property="og:title"]/@content',
            'og:description' => '//meta[@property="og:description"]/@content',
            'og:image' => '//meta[@property="og:image"]/@content',
        ];

        foreach ($metaTags as $key => $query) {
            $result = $xpath->query($query);
            if ($result && $result->length > 0) {
                $metadata[$key] = trim($result->item(0)->nodeValue);
            }
        }

        // Canonical URL
        $canonical = $xpath->query('//link[@rel="canonical"]/@href');
        if ($canonical && $canonical->length > 0) {
            $metadata['canonical'] = trim($canonical->item(0)->nodeValue);
        }

        return $metadata;
    }

    /**
z     * Extract ALL text from HTML (including header, footer, nav, everything)
     * This is used for keyword searching where we want maximum coverage
     */
    public function extractAllText(string $html): string
    {
        $dom = $this->loadHtml($html);
        if (!$dom) {
            return '';
        }

        // Remove only scripts, styles, and other non-content elements
        $xpath = new DOMXPath($dom);
        $selectorsToRemove = [
            '//script',
            '//style',
            '//noscript',
            '//iframe',
        ];

        foreach ($selectorsToRemove as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes !== false) {
                foreach ($nodes as $node) {
                    $node->parentNode?->removeChild($node);
                }
            }
        }

        // Extract all text from body
        $body = $dom->getElementsByTagName('body')->item(0);
        if ($body) {
            return $this->cleanText($this->extractTextFromNode($body));
        }

        return '';
    }

    /**
     * Remove boilerplate content (header, footer, sidebar, nav)
     */
    public function removeBoilerplate(string $html): string
    {
        // FIRST: Remove script, style, and noscript tags with REGEX before DOM parsing
        // This prevents JavaScript code from appearing in extracted text
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html);
        $html = preg_replace('/<noscript\b[^>]*>.*?<\/noscript>/is', '', $html);

        $dom = $this->loadHtml($html);
        if (!$dom) {
            return $html;
        }

        $xpath = new DOMXPath($dom);

        // Elements to remove (scripts already removed above)
        $selectorsToRemove = [
            '//header',
            '//footer',
            '//nav',
            '//aside',
            '//iframe',
            '//*[@class*="cookie"]',
            '//*[@id*="cookie"]',
            '//*[@class*="banner"]',
            '//*[@class*="advertisement"]',
            '//*[@class*="ad-"]',
            '//*[@class*="sidebar"]',
            '//*[@class*="menu"]',
            '//*[@role="navigation"]',
            '//*[@role="complementary"]',
        ];

        foreach ($selectorsToRemove as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes !== false) {
                foreach ($nodes as $node) {
                    $node->parentNode?->removeChild($node);
                }
            }
        }

        return $dom->saveHTML() ?: $html;
    }

    /**
     * Extract headings with hierarchy
     */
    private function extractHeadings(DOMDocument $dom): array
    {
        $headings = [];

        for ($level = 1; $level <= 6; $level++) {
            $tags = $dom->getElementsByTagName("h{$level}");
            foreach ($tags as $tag) {
                $text = trim($tag->textContent);
                if (!empty($text)) {
                    $headings[] = [
                        'level' => $level,
                        'text' => $text,
                    ];
                }
            }
        }

        return $headings;
    }

    /**
     * Extract internal links
     */
    private function extractLinks(DOMDocument $dom): array
    {
        $links = [];
        $anchors = $dom->getElementsByTagName('a');

        foreach ($anchors as $anchor) {
            $href = $anchor->getAttribute('href');
            $text = trim($anchor->textContent);

            if (!empty($href) && !empty($text)) {
                $links[] = [
                    'url' => $href,
                    'text' => $text,
                ];
            }
        }

        return array_slice($links, 0, 50); // Limit to 50 links
    }

    /**
     * Extract images with alt text
     */
    private function extractImages(DOMDocument $dom): array
    {
        $images = [];
        $imgs = $dom->getElementsByTagName('img');

        foreach ($imgs as $img) {
            $src = $img->getAttribute('src');
            $alt = $img->getAttribute('alt');

            if (!empty($src)) {
                $images[] = [
                    'src' => $src,
                    'alt' => $alt ?: '',
                ];
            }
        }

        return array_slice($images, 0, 20); // Limit to 20 images
    }

    /**
     * Extract navigation menu items with URLs and labels
     */
    public function extractMenuItems(string $html): array
    {
        $dom = $this->loadHtml($html);
        if (!$dom) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $menuItems = [];

        // Selectors for common menu structures (ordered by specificity)
        $menuSelectors = [
            // Standard HTML5 nav element
            '//nav//a',
            '//header//nav//a',

            // Class-based selectors (most common)
            '//ul[contains(@class, "menu")]//a',
            '//ul[contains(@class, "nav")]//a',
            '//ul[contains(@class, "navbar")]//a',
            '//ol[contains(@class, "menu")]//a',
            '//ol[contains(@class, "nav")]//a',
            '//div[contains(@class, "menu")]//a',
            '//div[contains(@class, "nav")]//a',
            '//div[contains(@class, "navbar")]//a',

            // ID-based selectors
            '//ul[contains(@id, "menu")]//a',
            '//ul[contains(@id, "nav")]//a',
            '//div[contains(@id, "menu")]//a',
            '//div[contains(@id, "nav")]//a',

            // Role-based selectors (accessibility)
            '//*[@role="navigation"]//a',

            // Common framework patterns
            '//ul[contains(@class, "uk-nav")]//a',  // UIKit
            '//ul[contains(@class, "uk-navbar")]//a',
            '//nav[contains(@class, "navbar")]//a',  // Bootstrap
            '//ul[contains(@class, "main-menu")]//a',
            '//ul[contains(@class, "primary-menu")]//a',
            '//ul[contains(@class, "elementor-nav-menu")]//a',  // Elementor
        ];

        foreach ($menuSelectors as $selector) {
            $nodes = $xpath->query($selector);
            if ($nodes !== false && $nodes->length > 0) {
                foreach ($nodes as $node) {
                    $href = $node->getAttribute('href');
                    $label = trim($node->textContent);

                    if (!empty($href) && !empty($label)) {
                        // Skip anchors, javascript, mailto, tel
                        if (preg_match('/^(#|javascript:|mailto:|tel:)/', $href)) {
                            continue;
                        }

                        $menuItems[] = [
                            'url' => $href,
                            'label' => $label,
                        ];
                    }
                }
            }
        }

        // Remove duplicates based on URL
        $uniqueItems = [];
        $seenUrls = [];
        foreach ($menuItems as $item) {
            $normalizedUrl = strtolower(trim($item['url']));
            if (!in_array($normalizedUrl, $seenUrls)) {
                $uniqueItems[] = $item;
                $seenUrls[] = $normalizedUrl;
            }
        }

        Log::channel('webscraper')->info('HtmlParser: Extracted menu items', [
            'total_found' => count($menuItems),
            'unique_items' => count($uniqueItems),
        ]);

        return array_slice($uniqueItems, 0, 50); // Limit to 50 menu items
    }

    /**
     * Extract search forms from HTML
     * Returns array of search forms with action, method, and input name
     */
    public function extractSearchForms(string $html): array
    {
        $dom = $this->loadHtml($html);
        if (!$dom) {
            return [];
        }

        $xpath = new DOMXPath($dom);
        $searchForms = [];

        // Find all forms
        $forms = $dom->getElementsByTagName('form');

        foreach ($forms as $form) {
            $action = $form->getAttribute('action');
            $method = $form->getAttribute('method') ?: 'GET';

            // Look for search-related attributes
            $formId = strtolower($form->getAttribute('id'));
            $formClass = strtolower($form->getAttribute('class'));
            $formRole = strtolower($form->getAttribute('role'));

            // Check if it's a search form
            $isSearchForm = false;
            if (preg_match('/(search|cerca|ricerca)/i', $formId . ' ' . $formClass . ' ' . $formRole)) {
                $isSearchForm = true;
            }

            // Look for search inputs
            $inputs = $form->getElementsByTagName('input');
            foreach ($inputs as $input) {
                $inputType = strtolower($input->getAttribute('type'));
                $inputName = $input->getAttribute('name');
                $inputId = strtolower($input->getAttribute('id'));
                $inputClass = strtolower($input->getAttribute('class'));
                $inputPlaceholder = strtolower($input->getAttribute('placeholder'));

                // Check if input is search-related
                if ($inputType === 'search' ||
                    preg_match('/(search|query|q|cerca|ricerca|keyword)/i', $inputName . ' ' . $inputId . ' ' . $inputClass . ' ' . $inputPlaceholder)) {

                    $searchForms[] = [
                        'action' => $action ?: '',
                        'method' => strtoupper($method),
                        'input_name' => $inputName,
                        'input_type' => $inputType ?: 'text',
                    ];

                    $isSearchForm = true;
                    break; // Found search input, no need to check others
                }
            }
        }

        return array_slice($searchForms, 0, 5); // Limit to 5 search forms
    }

    /**
     * Extract structured content (paragraphs, lists, etc.)
     */
    private function extractStructuredContent(DOMDocument $dom): array
    {
        $content = [];

        // Remove boilerplate
        $xpath = new DOMXPath($dom);

        // Extract paragraphs
        $paragraphs = $dom->getElementsByTagName('p');
        $paragraphTexts = [];
        foreach ($paragraphs as $p) {
            $text = trim($p->textContent);
            if (strlen($text) > 50) { // Minimum length
                $paragraphTexts[] = $text;
            }
        }
        $content['paragraphs'] = array_slice($paragraphTexts, 0, 20);

        // Extract lists
        $lists = [];
        foreach (['ul', 'ol'] as $listType) {
            $listElements = $dom->getElementsByTagName($listType);
            foreach ($listElements as $list) {
                $items = [];
                $liElements = $list->getElementsByTagName('li');
                foreach ($liElements as $li) {
                    $items[] = trim($li->textContent);
                }
                if (!empty($items)) {
                    $lists[] = [
                        'type' => $listType,
                        'items' => $items,
                    ];
                }
            }
        }
        $content['lists'] = array_slice($lists, 0, 10);

        return $content;
    }

    /**
     * Extract text from a DOM node recursively
     */
    private function extractTextFromNode($node): string
    {
        if (!$node) {
            return '';
        }

        $text = '';
        foreach ($node->childNodes as $child) {
            if ($child->nodeType === XML_TEXT_NODE) {
                $text .= ' ' . $child->textContent;
            } elseif ($child->nodeType === XML_ELEMENT_NODE) {
                // Add spacing for block elements
                if (in_array($child->nodeName, ['p', 'div', 'br', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
                    $text .= "\n";
                }
                $text .= $this->extractTextFromNode($child);
            }
        }

        return $text;
    }

    /**
     * Clean and normalize text
     */
    private function cleanText(string $text): string
    {
        // Remove multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove multiple newlines
        $text = preg_replace('/\n+/', "\n", $text);

        // Trim
        $text = trim($text);

        return $text;
    }

    /**
     * Load HTML into DOMDocument
     */
    private function loadHtml(string $html): ?DOMDocument
    {
        if (empty($html)) {
            return null;
        }

        $dom = new DOMDocument();

        // Suppress warnings for malformed HTML
        libxml_use_internal_errors(true);

        // Load HTML with UTF-8 encoding
        $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        // Clear errors
        libxml_clear_errors();

        return $dom;
    }
}