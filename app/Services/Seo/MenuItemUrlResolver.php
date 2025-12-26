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

        return $this->resolveHref($href);
    }

    public function resolveHref(string $href): string
    {
        $href = trim($href);
        if ($href === '') {
            throw new \InvalidArgumentException('MenuItem href mancante.');
        }

        if (preg_match('#^https?://#i', $href) === 1) {
            return $href;
        }

        // Preferisci l'host corrente quando sei in HTTP request (es. Filament),
        // così non dipendi da APP_URL/ config('app.url') eventualmente non allineato.
        $base = '';
        if (! app()->runningInConsole()) {
            try {
                $base = request()->getSchemeAndHttpHost();
            } catch (\Throwable $e) {
                $base = '';
            }
        }

        if (trim($base) === '') {
            $baseConfig = config('app.url');
            $base = is_string($baseConfig) ? $baseConfig : '';
        }
        if (trim($base) === '') {
            throw new \InvalidArgumentException('config(app.url) mancante: impossibile risolvere URL assoluto.');
        }

        return rtrim($base, '/').'/'.ltrim($href, '/');
    }
}
