<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Throwable;

class MigrateMediaToS3Command extends Command
{
    protected $signature = 'media:migrate-to-s3
                            {--dry-run : Affiche les fichiers sans les envoyer}
                            {--source= : Dossier source (défaut: publication/uploads)}';

    protected $description = 'Copie les médias locaux (uploads) vers le disque S3 configuré';

    public function handle(): int
    {
        $disk = (string) config('chrononews.media.disk', 'local');

        if (! in_array($disk, ['s3', 'media'], true)) {
            $this->error('MEDIA_DISK doit être "s3" ou "media" pour cette commande.');

            return self::FAILURE;
        }

        $source = $this->option('source');
        if (! $source) {
            $candidates = [
                rtrim((string) config('chrononews.media.local_root'), '/').'/uploads',
                dirname(base_path()).'/publication/uploads',
            ];
            foreach ($candidates as $candidate) {
                if (is_dir($candidate)) {
                    $source = $candidate;
                    break;
                }
            }
        }

        if (! is_string($source) || ! is_dir($source)) {
            $this->error('Dossier source introuvable. Utilisez --source=/chemin/vers/uploads');

            return self::FAILURE;
        }

        $files = File::allFiles($source);
        $dryRun = (bool) $this->option('dry-run');
        $uploaded = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($files as $file) {
            if ($file->isDir()) {
                continue;
            }

            $relative = 'uploads/'.str_replace('\\', '/', $file->getRelativePathname());

            if (Storage::disk($disk)->exists($relative)) {
                $skipped++;
                $this->line("Ignoré (déjà présent) : {$relative}");

                continue;
            }

            if ($dryRun) {
                $this->line("[dry-run] {$relative}");
                $uploaded++;

                continue;
            }

            try {
                $contents = file_get_contents($file->getPathname());
                if ($contents === false) {
                    $this->warn("Lecture impossible : {$file->getPathname()}");
                    $failed++;

                    continue;
                }

                $ok = Storage::disk($disk)->put($relative, $contents);

                if (! $ok || ! Storage::disk($disk)->exists($relative)) {
                    $this->error("Échec upload (réponse vide) : {$relative}");
                    $failed++;

                    continue;
                }

                $uploaded++;
                $this->line("Envoyé : {$relative}");
            } catch (Throwable $e) {
                $failed++;
                $this->error("Échec : {$relative} — {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Terminé. {$uploaded} envoyé(s), {$skipped} ignoré(s), {$failed} échec(s).");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
