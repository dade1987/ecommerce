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
                            {--quality=60 : La qualità della compressione dell\'immagine (1-100).}
                            {--dry-run : Esegue una simulazione senza modificare i file.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ottimizza le immagini nella cartella public e gestisce i backup.';

    /**
     * Percorso della cartella di backup.
     *
     * @var string
     */
    protected $backupPath;

    public function __construct()
    {
        parent::__construct();
        // Definiamo il percorso di backup nello storage, non accessibile pubblicamente.
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

        foreach ($images as $image) {
            $originalPath = $image->getRealPath();
            $relativePath = str_replace(public_path() . '/', '', $originalPath);
            $currentBackupPath = $this->backupPath . '/' . $relativePath;

            // Creiamo il backup se non esiste già
            if (!File::exists($currentBackupPath)) {
                File::ensureDirectoryExists(dirname($currentBackupPath));
                File::copy($originalPath, $currentBackupPath);
            }

            $originalSize = File::size($originalPath);

            if (!$dryRun) {
                try {
                    Image::make($originalPath)->save($originalPath, $quality);
                } catch (\Exception $e) {
                    $this->error("Errore durante l'ottimizzazione di {$relativePath}: " . $e->getMessage());
                    continue;
                }
            }

            $newSize = $dryRun ? $originalSize * ($quality / 100) : File::size($originalPath); // Stima grossolana per il dry-run
            $reduction = $originalSize - $newSize;
            $totalReduction += $reduction;
            $processedCount++;

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if ($processedCount > 0) {
            $this->info("Ottimizzazione completata per {$processedCount} immagini.");
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
        $this->info('--- FINE RIPRISTINO ---');

        return 0;
    }

    /**
     * Trova tutte le immagini nella cartella public.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getImages()
    {
        $files = collect(File::allFiles(public_path()));

        return $files->filter(function (SplFileInfo $file) {
            $supportedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            return in_array(strtolower($file->getExtension()), $supportedExtensions);
        });
    }
}
