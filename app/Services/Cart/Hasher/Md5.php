<?php

namespace App\Services\Cart\Hasher;

use Illuminate\Support\Arr;
use App\Services\Cart\Contracts\Hasher as HashContract;

class Md5 implements HashContract
{
    /**
     * @param mixed $id
     * @param array $parameters
     * @return string
     */
    public function make($id, array $parameters): string
    {
        return md5($id . serialize(Arr::sortRecursive($parameters)));
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'md5';
    }
}