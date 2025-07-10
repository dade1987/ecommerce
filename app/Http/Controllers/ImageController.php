<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;

class ImageController extends Controller
{
    public function show($path)
    {
        // Il path ora è il percorso completo dal web root, es. "curator/media/foo.jpeg"
        $originalPath = public_path($path);
        $optimizedPath = public_path('storage/optimized/' . $path);

        // Verifichiamo se l'immagine originale esiste
        if (!File::exists($originalPath)) {
            abort(404, 'Immagine non trovata.');
        }
        
        // La nostra regola .htaccess gestisce già il caching,
        // quindi se lo script viene eseguito, significa che dobbiamo creare l'immagine.

        try {
            // Assicuriamoci che la directory di destinazione esista
            $directory = dirname($optimizedPath);
            if (!File::isDirectory($directory)) {
                File::makeDirectory($directory, 0755, true, true);
            }

            // Usiamo Intervention Image per processare l'immagine
            $image = Image::make($originalPath);

            // Ridimensioniamo se l'immagine è molto grande
            $image->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });

            // Codifichiamo e salviamo
            $image->save($optimizedPath, 60, 'jpg');
            
            // Restituiamo l'immagine appena creata
            return $image->response();

        } catch (\Exception $e) {
            abort(500, "Impossibile processare l'immagine: " . $e->getMessage());
        }
    }
}
