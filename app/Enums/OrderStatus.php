<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum OrderStatus: string implements HasLabel, HasColor, HasIcon
{
    case PENDING = 'in_attesa';
    case PROCESSING = 'in_produzione';
    case COMPLETED = 'completato';
    case CANCELLED = 'annullato';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::PENDING => 'In attesa',
            self::PROCESSING => 'In produzione',
            self::COMPLETED => 'Completato',
            self::CANCELLED => 'Annullato',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::PROCESSING => 'info',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::PENDING => 'heroicon-o-clock',
            self::PROCESSING => 'heroicon-o-cog',
            self::COMPLETED => 'heroicon-o-check-circle',
            self::CANCELLED => 'heroicon-o-x-circle',
        };
    }
} 