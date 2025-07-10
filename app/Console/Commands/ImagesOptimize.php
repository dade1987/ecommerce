<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Intervention\Image\ImageManagerStatic as Image;
use Symfony\Component\Finder\SplFileInfo;

class ImagesOptimize extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:optimize
                            {--restore : Ripristina le immagini originali dai backup.}
                            {--quality=75 : La qualità di compressione per i file JPG (1-100).}
                            {--convert-png : Converte i PNG di grandi dimensioni (>500KB) in JPG per una riduzione massima.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ottimizza le immagini in-place, con backup. Include opzione per convertire PNG di grandi dimensioni.';

    /**
     * The path to the backup directory.
     *
     * @var string
     */
    protected $backupPath;

    public function __construct()
    {
        parent::__construct();
        $this->backupPath = storage_path('app/image-backups');
    }

    public function handle()
    {
        if ($this->option('restore')) {
            return $this->restoreImages();
        }

        return $this->optimizeImages();
    }

    protected function optimizeImages()
    {
        $quality = (int) $this->option('quality');
        $convertPng = $this->option('convert-png');

        $this->info('--- INIZIO OTTIMIZZAZIONE IN-PLACE ---');
        File::ensureDirectoryExists($this->backupPath);

        $images = $this->getImages();
        if ($images->isEmpty()) {
            $this->info('Nessuna immagine trovata da ottimizzare.');
            return 0;
        }

        $this->info("Trovate {$images->count()} immagini da processare...");
        $totalReduction = 0;
        $processedCount = 0;
        $convertedCount = 0;

        foreach ($images as $image) {
            $path = $image->getRealPath();
            $relativePath = $image->getRelativePathname();
            
            try {
                // 1. Backup
                $backupFilePath = $this->backupPath . '/' . $relativePath;
                if (!File::exists($backupFilePath)) {
                    File::ensureDirectoryExists(dirname($backupFilePath));
                    File::copy($path, $backupFilePath);
                }

                // 2. Ottimizzazione
                clearstatcache(true, $path);
                $originalSize = filesize($path);

                $img = Image::make($path);
                $extension = strtolower($image->getExtension());
                $actionTaken = 'Nessuna';

                if (in_array($extension, ['jpg', 'jpeg'])) {
                    $img->save($path, $quality);
                    $actionTaken = 'Compresso JPG';
                } elseif ($extension === 'png') {
                    // Se l'opzione è attiva e il file è grande, converti.
                    if ($convertPng && $originalSize > 500 * 1024) { // Soglia 500KB
                        $newPath = substr($path, 0, strrpos($path, '.')) . '.jpg';
                        
                        // Il backup dell'originale .png è già stato fatto.
                        $img->fill('#ffffff')->save($newPath, $quality);
                        File::delete($path); // Rimuovi il vecchio .png
                        
                        $path = $newPath; // Aggiorna il path per il controllo dimensione
                        $actionTaken = 'Convertito PNG > JPG';
                        $convertedCount++;
                    } else {
                        // Altrimenti, compressione lossless standard per i PNG
                        $img->save($path, 9);
                        $actionTaken = 'Compresso PNG (Lossless)';
                    }
                } else {
                    $img->save($path); // Altri formati
                    $actionTaken = 'Salvato';
                }

                clearstatcache(true, $path);
                $newSize = filesize($path);
                $reduction = $originalSize - $newSize;

                if ($reduction > 1024) { // Mostra solo se il risparmio è significativo
                    $this->line("<info>Processato:</info> {$relativePath} | <comment>Risparmio:</comment> " . round($reduction / 1024) . " KB | <comment>Azione:</comment> {$actionTaken}");
                    $totalReduction += $reduction;
                }
                $processedCount++;

            } catch (\Exception $e) {
                $this->error("\nErrore processando {$relativePath}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Ottimizzazione completata.");
        $this->info("Immagini processate: {$processedCount}");
        if ($convertedCount > 0) {
            $this->info("Immagini convertite da PNG a JPG: {$convertedCount}");
        }
        $this->info("Riduzione totale dello spazio: " . round($totalReduction / 1024 / 1024, 2) . " MB.");
        $this->comment('I backup sono in: storage/app/image-backups. Per ripristinare, usa --restore.');

        return 0;
    }

    protected function restoreImages()
    {
        $this->info('--- INIZIO RIPRISTINO IMMAGINI DAI BACKUP ---');
        if (!File::isDirectory($this->backupPath)) {
            $this->error('Cartella di backup non trovata.');
            return 1;
        }
        
        $backupFiles = collect(File::allFiles($this->backupPath));
        if ($backupFiles->isEmpty()) {
            $this->info('Nessun backup trovato.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($backupFiles->count());
        $progressBar->start();

        foreach ($backupFiles as $backupFile) {
            $relativePath = $backupFile->getRelativePathname();
            $originalPath = public_path($relativePath);
            
            File::ensureDirectoryExists(dirname($originalPath));
            File::copy($backupFile->getRealPath(), $originalPath);
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Ripristino completato per {$backupFiles->count()} immagini.");
        return 0;
    }

    protected function getImages()
    {
        return collect(File::allFiles(public_path()))->filter(function (SplFileInfo $file) {
            return in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        });
    }
}
