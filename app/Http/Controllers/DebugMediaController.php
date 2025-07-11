<?php

namespace App\Http\Controllers;

use Awcodes\Curator\Http\Controllers\MediaController as BaseMediaController;
use Awcodes\Curator\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;

class DebugMediaController extends BaseMediaController
{
    public function show(Request $request, $path)
    {
        Log::debug('-------------------- Inizio richiesta MediaController --------------------');
        Log::debug('Request Path: ' . $path);
        Log::debug('Request Data: ', $request->all());

        $routeBasePath = Str::of(config('curator.glide.route_path', 'curator'))
            ->trim('/')
            ->prepend('/')
            ->append('/')
            ->toString();

        Log::debug('Route Base Path: ' . $routeBasePath);

        try {
            Log::debug('Validazione firma...');
            SignatureFactory::create(config('app.key'))->validateRequest($routeBasePath . $path, $request->all());
            Log::debug('Firma valida.');
        } catch (SignatureException $e) {
            Log::error('Errore di firma: ' . $e->getMessage());
            abort(403);
        } catch (FileNotFoundException $e) {
            Log::error('File non trovato (FileNotFoundException): ' . $e->getMessage());
            abort(404);
        }

        $mediaModel = config('curator.model', Media::class);

        Log::debug('Ricerca media nel database con path: ' . $path);
        $media = $mediaModel::query()->where('path', $path)->first();

        if (! $media) {
            Log::warning('Media non trovato nel database per il path: ' . $path);
            // Anche se non trovato, potremmo comunque provare a servirlo con Glide se è solo un file su disco
        } else {
            Log::debug('Media trovato: ', $media->toArray());
        }

        if ($media && ! $media->resizable) {
            Log::debug('Media non ridimensionabile. Invio diretto dal disco.');
            Log::debug('Disco: ' . $media->disk);
            Log::debug('Path: ' . $media->path);
            $storage = Storage::disk($media->disk);
            $filePath = $storage->path($media->path);
            Log::debug('Percorso fisico calcolato del file: ' . $filePath);
            Log::debug('Esistenza del file su disco: ' . ($storage->exists($media->path) ? 'Sì' : 'No'));

            return $storage->response($media->path);
        }

        Log::debug('Media ridimensionabile o non trovato nel DB. Utilizzo di Glide.');

        $serverFactoryClass = config('curator.glide.server');
        Log::debug('Glide Server Factory: ' . $serverFactoryClass);

        $server = app($serverFactoryClass)->getFactory();
        $server->setBaseUrl($routeBasePath);

        // Debug del filesystem di Glide
        $source = $server->getSource();
        if (method_exists($source, 'getDriver')) {
            $driver = $source->getDriver();
            if (method_exists($driver, 'getAdapter')) {
                $adapter = $driver->getAdapter();
                if (method_exists($adapter, 'getPathPrefix')) {
                    $prefix = $adapter->getPathPrefix();
                    Log::debug('Percorso sorgente di Glide (prefix): ' . $prefix);
                }
                if (method_exists($adapter, 'getFilesystem')) {
                    $fs = $adapter->getFilesystem();
                     if (method_exists($fs, 'getConfig')) {
                        $config = $fs->getConfig();
                        Log::debug('Configurazione Filesystem di Glide: ', (array) $config);
                     }
                }
            }
        }

        $sourcePath = $server->getSourcePath($path);
        Log::debug('Percorso che Glide proverà a risolvere (relativo al suo disco): ' . $sourcePath);
        Log::debug('Esistenza del file per Glide: ' . ($source->exists($sourcePath) ? 'Sì' : 'No'));
        
        Log::debug('Chiamata a getImageResponse di Glide con path: ' . $path);
        Log::debug('-------------------- Fine richiesta MediaController --------------------');

        return $server->getImageResponse($path, request()->all());
    }
} 