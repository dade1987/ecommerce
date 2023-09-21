<?php

namespace App\Services\Cart\Contracts;

interface ItemContract
{
    /**
     * Return item identifier.
     */
    public function getRowId(): string;
}