<?php

namespace Tests\Unit\Seo;

use App\Models\MenuItem;
use App\Services\Seo\MenuItemUrlResolver;
use Tests\TestCase;

class MenuItemUrlResolverTest extends TestCase
{
    public function test_resolves_relative_href_using_app_url(): void
    {
        // Arrange
        config(['app.url' => 'https://example.test']);
        $item = new MenuItem(['href' => '/landing/prodotto']);

        // Act
        $url = app(MenuItemUrlResolver::class)->resolve($item);

        // Assert
        $this->assertSame('https://example.test/landing/prodotto', $url);
    }

    public function test_returns_absolute_href_unchanged(): void
    {
        // Arrange
        config(['app.url' => 'https://example.test']);
        $item = new MenuItem(['href' => 'https://altro-dominio.test/x']);

        // Act
        $url = app(MenuItemUrlResolver::class)->resolve($item);

        // Assert
        $this->assertSame('https://altro-dominio.test/x', $url);
    }

    public function test_resolve_href_works_with_relative_string(): void
    {
        // Arrange
        config(['app.url' => 'https://example.test']);

        // Act
        $url = app(MenuItemUrlResolver::class)->resolveHref('/foo');

        // Assert
        $this->assertSame('https://example.test/foo', $url);
    }
}
