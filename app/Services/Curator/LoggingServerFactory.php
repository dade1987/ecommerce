<?php

namespace App\Services\Curator;

use Awcodes\Curator\Glide\Contracts\ServerFactory as ServerFactoryContract;
use Illuminate\Support\Facades\Log;
use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Server;
use League\Glide\ServerFactory;

class LoggingServerFactory implements ServerFactoryContract
{
    public function getFactory(): ServerFactory|Server
    {
        $sourcePath = storage_path('app');
        $sourcePathPrefix = 'public';

        Log::info('Curator source path: ' . $sourcePath . '/' . $sourcePathPrefix);

        return ServerFactory::create([
            'driver' => 'gd',
            'response' => new SymfonyResponseFactory(app('request')),
            'source' => $sourcePath,
            'source_path_prefix' => $sourcePathPrefix,
            'cache' => storage_path('app'),
            'cache_path_prefix' => '.cache',
            'max_image_size' => 2000 * 2000,
        ]);
    }
} 