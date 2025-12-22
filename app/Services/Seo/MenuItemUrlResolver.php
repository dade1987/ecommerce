<?php

namespace App\Services\Seo;

use App\Models\MenuItem;
use function Safe\preg_match;

class MenuItemUrlResolver
{
    public function resolve(MenuItem $menuItem): string
    {
        $rawHref = $menuItem->href;
        $href = trim(is_string($rawHref) ? $rawHref : '');
        if ($href === '') {
            throw new \InvalidArgumentException('MenuItem href mancante.');
        }

        if (preg_match('#^https?://#i', $href) === 1) {
            return $href;
        }

        $baseConfig = config('app.url');
        $base = is_string($baseConfig) ? $baseConfig : '';
        if (trim($base) === '') {
            throw new \InvalidArgumentException('config(app.url) mancante: impossibile risolvere URL assoluto.');
        }

        return rtrim($base, '/').'/'.ltrim($href, '/');
    }
}
