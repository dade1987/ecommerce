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
        $routeBasePath = Str::of(config('curator.glide.route_path', 'curator'))
            ->trim('/')
            ->prepend('/')
            ->append('/')
            ->toString();

        try {
            SignatureFactory::create(config('app.key'))->validateRequest($routeBasePath . $path, $request->all());
        } catch (SignatureException $e) {
            Log::warning('Media signature validation failed', ['path' => $path]);
            abort(403);
        } catch (FileNotFoundException $e) {
            abort(404);
        }

        $mediaModel = config('curator.model', Media::class);
        $media = $mediaModel::query()->where('path', $path)->first();

        if ($media && ! $media->resizable) {
            $storage = Storage::disk($media->disk);
            return $storage->response($media->path);
        }

        $serverFactoryClass = config('curator.glide.server');
        $server = app($serverFactoryClass)->getFactory();
        $server->setBaseUrl($routeBasePath);

        return $server->getImageResponse($path, request()->all());
    }
}