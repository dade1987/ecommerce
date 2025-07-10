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
                            {--quality=75 : La qualità della compressione per i JPG (1-100).}
                            {--convert-large-pngs : Converte i PNG di grandi dimensioni in JPG.}
                            {--conversion-threshold=500 : La soglia in KB per considerare un PNG "grande" (default: 500).}
                            {--dry-run : Esegue una simulazione senza modificare i file.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ottimizza le immagini (JPG, PNG) nella cartella public, con backup e opzione di conversione.';

    /**
     * Percorso della cartella di backup.
     *
     * @var string
     */
    protected $backupPath;

    public function __construct()
    {
        parent::__construct();
        $this->backupPath = storage_path('app/image-backups');
    }

    /**
     * Execute the console command.
     */
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
        $dryRun = $this->option('dry-run');
        $convertPngs = $this->option('convert-large-pngs');
        $conversionThreshold = (int) $this->option('conversion-threshold') * 1024; // in bytes

        if ($quality <= 0 || $quality > 100) {
            $this->error('La qualità deve essere un numero tra 1 e 100.');
            return 1;
        }

        $this->info($dryRun ? '--- INIZIO SIMULAZIONE (DRY RUN) ---' : '--- INIZIO OTTIMIZZAZIONE IMMAGINI ---');
        
        File::ensureDirectoryExists($this->backupPath);

        $images = $this->getImages();
        
        if ($images->isEmpty()) {
            $this->info('Nessuna immagine trovata da ottimizzare.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($images->count());
        $progressBar->start();

        $totalReduction = 0;
        $processedCount = 0;
        $convertedCount = 0;

        foreach ($images as $image) {
            $originalPath = $image->getRealPath();
            $relativePath = str_replace(public_path() . '/', '', $originalPath);
            $extension = strtolower($image->getExtension());
            
            // Crea il backup se non esiste già
            $currentBackupPath = $this->backupPath . '/' . $relativePath;
            if (!File::exists($currentBackupPath)) {
                File::ensureDirectoryExists(dirname($currentBackupPath));
                File::copy($originalPath, $currentBackupPath);
            }

            $originalSize = File::size($originalPath);
            $newSize = $originalSize;

            if (!$dryRun) {
                try {
                    $img = Image::make($originalPath);
                    
                    if ($extension === 'jpg' || $extension === 'jpeg') {
                        $img->save($originalPath, $quality);
                        $newSize = File::size($originalPath);
                    } elseif ($extension === 'png') {
                        // Prima ottimizzazione lossless per PNG
                        $img->save($originalPath, 9); // Compression level 9 for PNG
                        $newSize = File::size($originalPath);

                        // Conversione opzionale se il file è ancora grande
                        if ($convertPngs && $newSize > $conversionThreshold) {
                            $newPathJpg = substr($originalPath, 0, strlen($originalPath) - 3) . 'jpg';
                            
                            // Aggiunge uno sfondo bianco per evitare nero su trasparenza
                            $img->fill('#ffffff');
                            
                            $img->save($newPathJpg, $quality);
                            $newSize = File::size($newPathJpg);
                            
                            // Elimina il vecchio file PNG dopo la conversione
                            File::delete($originalPath);
                            
                            $this->line("\n<comment>Convertito:</comment> {$relativePath} -> " . basename($newPathJpg) . " (Risparmio: " . round(($originalSize - $newSize) / 1024) . " KB)");
                            $convertedCount++;
                        }
                    }
                    
                    $processedCount++;
                } catch (\Exception $e) {
                    $this->error("\nErrore durante l'ottimizzazione di {$relativePath}: " . $e->getMessage());
                }
            } else {
                // Simulazione per dry-run
                if ($extension === 'jpg' || $extension === 'jpeg') {
                    $newSize = $originalSize * ($quality / 100);
                }
                $processedCount++;
            }

            $totalReduction += ($originalSize - $newSize);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($processedCount > 0) {
            $this->info("Ottimizzazione completata per {$processedCount} immagini.");
            if ($convertedCount > 0) {
                $this->info("Immagini PNG convertite in JPG: {$convertedCount}.");
            }
            $this->info("Riduzione totale dello spazio: " . round($totalReduction / 1024 / 1024, 2) . " MB.");
        } else {
            $this->info("Nessuna nuova immagine da processare.");
        }
        
        $this->info($dryRun ? '--- FINE SIMULAZIONE ---' : '--- FINE OTTIMIZZAZIONE ---');

        return 0;
    }

    protected function restoreImages()
    {
        $this->info('--- INIZIO RIPRISTINO IMMAGINI DAI BACKUP ---');

        if (!File::isDirectory($this->backupPath)) {
            $this->error('La cartella di backup non è stata trovata.');
            return 1;
        }
        
        $backupFiles = collect(File::allFiles($this->backupPath));

        if ($backupFiles->isEmpty()) {
            $this->info('Nessun backup trovato da ripristinare.');
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
        $this->comment('I file di backup sono stati conservati. Puoi eliminarli manualmente dalla cartella storage/app/image-backups se non servono più.');
        $this->comment('Nota: Se avevi convertito dei PNG in JPG, i file JPG generati non vengono rimossi automaticamente.');
        $this->info('--- FINE RIPRISTINO ---');

        return 0;
    }

    /**
     * Trova tutte le immagini nella cartella public (JPG, JPEG, PNG).
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getImages()
    {
        $files = collect(File::allFiles(public_path()));

        return $files->filter(function (SplFileInfo $file) {
            // Ottimizziamo solo JPG e PNG per ora
            $supportedExtensions = ['jpg', 'jpeg', 'png'];
            return in_array(strtolower($file->getExtension()), $supportedExtensions);
        });
    }
}
