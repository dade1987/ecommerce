<?php

namespace App\Services\Curator;

use Awcodes\Curator\Glide\Contracts\ServerFactory as ServerFactoryContract;
use League\Glide\Server;
use League\Glide\ServerFactory;
use Psr\Http\Message\ServerRequestInterface;
use Illuminate\Support\Facades\Log;

class LoggingServerFactory implements ServerFactoryContract
{
    public function getFactory(): ServerFactory|Server
    {
        Log::info("LoggingServerFactory::getFactory() chiamato");
        
        return ServerFactory::create([
            'driver'          => 'gd',
            'response'        => new \League\Glide\Responses\SymfonyResponseFactory(app('request')),
            'source'          => storage_path('app/public'),
            'cache'           => storage_path('app/.cache'),
            'max_image_size'  => 2000 * 2000,
            'before_response' => function (Server $server, ServerRequestInterface $request) {
                Log::info("Callback before_response chiamato");
                // 'path' è settato da Glide come attribute PSR-7
                $path = $request->getAttribute('path');
                Log::info("Path attribute: " . ($path ?? 'null'));
                // ottoiene il percorso completo al file sorgente
                $fullPath = $server->getSourcePath($path);
                Log::info("Curator serving source file: {$fullPath}");
            },
        ]);
    }
} 