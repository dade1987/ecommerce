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
                            {--restore : Ripristina tutte le immagini originali dai backup.}
                            {--restore-folder= : Ripristina solo i file di una cartella specifica (es: media).}
                            {--quality=75 : La qualità di compressione per i file JPG (1-100).}
                            {--media-only : Processa solo le immagini nella cartella media/.}
                            {--target-size=50 : Dimensione target in KB per le immagini (default: 50).}
                            {--force : Forza la ri-ottimizzazione anche se esiste già un backup.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ottimizza le immagini con backup automatico. Usa --media-only per processare solo la cartella media.';

    /**
     * The path to the backup directory.
     *
     * @var string
     */
    protected $backupPath;

    /**
     * The path to the public storage directory.
     *
     * @var string
     */
    protected $publicStoragePath;

    public function __construct()
    {
        parent::__construct();
        $this->backupPath = storage_path('app/image-backups');
        $this->publicStoragePath = storage_path('app/public');
    }

    public function handle()
    {
        if ($this->option('restore')) {
            return $this->restoreImages();
        }

        if ($this->option('restore-folder')) {
            return $this->restoreFolder($this->option('restore-folder'));
        }

        return $this->optimizeImages();
    }

    protected function optimizeImages()
    {
        $quality = (int) $this->option('quality');
        $mediaOnly = $this->option('media-only');
        $targetSizeKB = (int) $this->option('target-size');
        $force = $this->option('force');

        $this->info('--- INIZIO OTTIMIZZAZIONE IN-PLACE ---');
        $this->info("Cercando immagini in: {$this->publicStoragePath}");
        
        $searchPath = $this->publicStoragePath;
        
        if ($mediaOnly) {
            $searchPath = $this->publicStoragePath . '/media';
            $this->info("Modalità MEDIA-ONLY attiva - cercando in: {$searchPath}");
            $this->info("Obiettivo dimensione: {$targetSizeKB} KB");
            
            // Debug: verifica se la cartella esiste
            if (!File::isDirectory($searchPath)) {
                $this->error("ERRORE: La cartella {$searchPath} non esiste!");
                $this->info("Cartelle disponibili in {$this->publicStoragePath}:");
                if (File::isDirectory($this->publicStoragePath)) {
                    foreach (File::directories($this->publicStoragePath) as $dir) {
                        $this->line("  - " . basename($dir));
                    }
                } else {
                    $this->error("Anche {$this->publicStoragePath} non esiste!");
                }
                return 1;
            }
        }
        
        File::ensureDirectoryExists($this->backupPath);

        $images = $this->getImages($mediaOnly);
        
        // Debug: mostra cosa ha trovato
        $this->info("Trovate {$images->count()} immagini totali");
        if ($images->count() > 0 && $images->count() <= 5) {
            $this->info("File trovati:");
            foreach ($images as $image) {
                $this->line("  - " . $image->getRelativePathname() . " (" . round(filesize($image->getRealPath()) / 1024) . " KB)");
            }
        }
        
        if ($images->isEmpty()) {
            $this->info('Nessuna immagine trovata da ottimizzare.');
            return 0;
        }

        $this->info("Iniziando processamento...");
        $totalReduction = 0;
        $processedCount = 0;
        $skippedCount = 0;

        foreach ($images as $image) {
            $path = $image->getRealPath();
            $relativePath = $image->getRelativePathname();
            
            try {
                // 1. Controlla se esiste già un backup
                $backupFilePath = $this->backupPath . '/' . $relativePath;
                
                if (File::exists($backupFilePath) && !$force) {
                    $skippedCount++;
                    $this->line("<comment>Saltato (backup esistente):</comment> {$relativePath}");
                    continue;
                }

                // 2. Crea backup se non esiste
                if (!File::exists($backupFilePath)) {
                    File::ensureDirectoryExists(dirname($backupFilePath));
                    File::copy($path, $backupFilePath);
                    $this->line("<comment>Backup creato:</comment> {$relativePath}");
                }

                // 3. Ottimizzazione aggressiva se media-only
                clearstatcache(true, $path);
                $originalSize = filesize($path);
                $originalSizeKB = round($originalSize / 1024);

                $this->line("<info>Processando:</info> {$relativePath} ({$originalSizeKB} KB)");

                if ($mediaOnly && $originalSizeKB > $targetSizeKB) {
                    $this->optimizeToTargetSize($path, $targetSizeKB, $relativePath);
                } else {
                    // Ottimizzazione normale
                    $this->standardOptimization($path, $quality);
                }

                clearstatcache(true, $path);
                $newSize = filesize($path);
                $newSizeKB = round($newSize / 1024);
                $reduction = $originalSize - $newSize;

                if ($reduction > 1024) {
                    $this->line("<info>Completato:</info> {$relativePath} | <comment>Prima:</comment> {$originalSizeKB} KB | <comment>Dopo:</comment> {$newSizeKB} KB | <comment>Risparmio:</comment> " . round($reduction / 1024) . " KB");
                    $totalReduction += $reduction;
                } else {
                    $this->line("<comment>Nessun risparmio significativo per:</comment> {$relativePath}");
                }
                $processedCount++;

            } catch (\Exception $e) {
                $this->error("\nErrore processando {$relativePath}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("Ottimizzazione completata.");
        $this->info("Immagini processate: {$processedCount}");
        $this->info("Immagini saltate (backup esistente): {$skippedCount}");
        $this->info("Riduzione totale dello spazio: " . round($totalReduction / 1024 / 1024, 2) . " MB.");
        $this->comment('I backup sono in: storage/app/image-backups. Per ripristinare, usa --restore o --restore-folder=nomecartella.');
        if ($skippedCount > 0) {
            $this->comment('Per ri-ottimizzare i file saltati, usa --force.');
        }

        return 0;
    }

    protected function optimizeToTargetSize($path, $targetSizeKB, $relativePath)
    {
        $img = Image::make($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        
        // Prova con qualità decrescente fino a raggiungere la dimensione target
        $qualities = [60, 50, 40, 30, 25, 20];
        
        foreach ($qualities as $quality) {
            $currentPath = $path; // Inizializza il percorso corrente
            
            if (in_array($extension, ['jpg', 'jpeg'])) {
                $img->save($currentPath, $quality);
            } elseif ($extension === 'png') {
                // Per PNG, converti in JPG per una compressione migliore
                $newPath = substr($path, 0, strrpos($path, '.')) . '.jpg';
                $img->fill('#ffffff')->save($newPath, $quality);
                File::delete($path);
                $currentPath = $newPath;
            }
            
            clearstatcache(true, $currentPath);
            $currentSizeKB = round(filesize($currentPath) / 1024);
            
            if ($currentSizeKB <= $targetSizeKB) {
                $this->line("<comment>Raggiunto target:</comment> {$relativePath} - {$currentSizeKB} KB (qualità: {$quality})");
                break;
            }
        }
    }

    protected function standardOptimization($path, $quality)
    {
        $img = Image::make($path);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if (in_array($extension, ['jpg', 'jpeg'])) {
            $img->save($path, $quality);
        } elseif ($extension === 'png') {
            $img->save($path, 9);
        } else {
            $img->save($path);
        }
    }

    protected function restoreImages()
    {
        $this->info('--- INIZIO RIPRISTINO TUTTE LE IMMAGINI DAI BACKUP ---');
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
            $originalPath = $this->publicStoragePath . '/' . $relativePath;
            
            File::ensureDirectoryExists(dirname($originalPath));
            File::copy($backupFile->getRealPath(), $originalPath);
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Ripristino completato per {$backupFiles->count()} immagini.");
        return 0;
    }

    protected function restoreFolder($folderName)
    {
        $this->info("--- INIZIO RIPRISTINO CARTELLA: {$folderName} ---");
        
        if (!File::isDirectory($this->backupPath)) {
            $this->error('Cartella di backup non trovata.');
            return 1;
        }
        
        $folderBackupPath = $this->backupPath . '/' . $folderName;
        if (!File::isDirectory($folderBackupPath)) {
            $this->error("Cartella di backup '{$folderName}' non trovata.");
            return 1;
        }
        
        $backupFiles = collect(File::allFiles($folderBackupPath));
        if ($backupFiles->isEmpty()) {
            $this->info("Nessun backup trovato nella cartella '{$folderName}'.");
            return 0;
        }

        $progressBar = $this->output->createProgressBar($backupFiles->count());
        $progressBar->start();

        foreach ($backupFiles as $backupFile) {
            $relativePath = $folderName . '/' . $backupFile->getRelativePathname();
            $originalPath = $this->publicStoragePath . '/' . $relativePath;
            
            File::ensureDirectoryExists(dirname($originalPath));
            File::copy($backupFile->getRealPath(), $originalPath);
            
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Ripristino completato per {$backupFiles->count()} immagini nella cartella '{$folderName}'.");
        return 0;
    }

    protected function getImages($mediaOnly = false)
    {
        $searchPath = $mediaOnly ? $this->publicStoragePath . '/media' : $this->publicStoragePath;
        
        if ($mediaOnly && !File::isDirectory($searchPath)) {
            return collect([]);
        }
        
        return collect(File::allFiles($searchPath))->filter(function (SplFileInfo $file) {
            return in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        });
    }
}
