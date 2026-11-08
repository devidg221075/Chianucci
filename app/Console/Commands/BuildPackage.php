<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class BuildPackage extends Command
{
    protected $signature = 'site:package {--output=dist : cartella di destinazione} {--vendor=install : install|copy|stub} {--skip-build : non ricompilare gli asset}';

    protected $description = 'Crea lo .zip completo (codice, librerie, asset, catalogo) da caricare sul server da cPanel';

    public function handle(): int
    {
        $out = $this->option('output');
        File::ensureDirectoryExists($out);
        $stage = sys_get_temp_dir().'/chianucci-stage-'.uniqid();
        $app = "$stage/chianucci_app";
        $pub = "$stage/public_html";
        File::ensureDirectoryExists($app);
        File::ensureDirectoryExists($pub);

        if (! $this->option('skip-build') && ! $this->shell('npm run build', base_path())) {
            return self::FAILURE;
        }

        // Codice dell'applicazione (niente test, documenti, logo originali, segreti, database locali).
        foreach (['app', 'bootstrap', 'config', 'database', 'lang', 'routes'] as $dir) {
            File::copyDirectory(base_path($dir), "$app/$dir");
        }
        File::copyDirectory(resource_path('views'), "$app/resources/views");
        foreach (['artisan', 'composer.json', 'composer.lock'] as $file) {
            File::copy(base_path($file), "$app/$file");
        }
        foreach (glob("$app/database/*.sqlite*") ?: [] as $db) {
            File::delete($db);
        }
        foreach (glob("$app/bootstrap/cache/*") ?: [] as $cache) {
            if (basename($cache) !== '.gitignore') {
                File::delete($cache);
            }
        }
        // Le cartelle devono esistere nello zip (le vuote non si salvano): un segnaposto le crea.
        File::ensureDirectoryExists("$app/bootstrap/cache");
        File::put("$app/bootstrap/cache/.gitignore", "*\n!.gitignore\n");
        foreach (['app/public', 'framework/cache/data', 'framework/sessions', 'framework/views', 'logs'] as $dir) {
            File::ensureDirectoryExists("$app/storage/$dir");
            File::put("$app/storage/$dir/.gitignore", "*\n!.gitignore\n");
        }

        // Cartella pubblica del dominio.
        File::copyDirectory(public_path(), $pub);
        foreach (['hot', 'index.php', 'storage'] as $skip) {
            File::isDirectory("$pub/$skip") ? File::deleteDirectory("$pub/$skip") : File::delete("$pub/$skip");
        }
        File::copy(resource_path('stubs/index.php.stub'), "$pub/index.php");
        if (File::isDirectory(storage_path('app/public'))) {
            File::copyDirectory(storage_path('app/public'), "$pub/storage");
            File::delete("$pub/storage/.gitignore");
        }

        // Catalogo e reindirizzamenti, senza prezzi.
        $this->callSilent('site:export-catalog', ['path' => "$app/database/catalog.json"]);

        File::copy(resource_path('stubs/env.production.stub'), "$app/.env.example");

        $token = bin2hex(random_bytes(12));
        File::put("$pub/install.php", str_replace('{{TOKEN_HASH}}', hash('sha256', $token), File::get(resource_path('stubs/install.php.stub'))));

        File::copy(base_path('docs/guida-pubblicazione.md'), "$stage/GUIDA-PUBBLICAZIONE.md");

        if (! $this->vendor($app)) {
            return self::FAILURE;
        }

        $zipPath = $out.'/chianucci-'.date('Ymd-His').'.zip';
        $this->zip($stage, $zipPath);
        File::deleteDirectory($stage);

        $this->info("Pacchetto creato: $zipPath (".round(filesize($zipPath) / 1048576, 1).' MB)');
        $this->warn("TOKEN: $token");
        $this->line('Il token serve una sola volta alla pagina install.php: conservalo, non è scritto nel pacchetto.');

        return self::SUCCESS;
    }

    private function vendor(string $app): bool
    {
        switch ($this->option('vendor')) {
            case 'copy':
                return File::copyDirectory(base_path('vendor'), "$app/vendor");
            case 'stub':
                File::ensureDirectoryExists("$app/vendor");
                File::put("$app/vendor/autoload.php", "<?php\n// segnaposto per i test del pacchetto\n");

                return true;
            default:
                return $this->shell('composer install --no-dev --optimize-autoloader --no-scripts --no-interaction', $app);
        }
    }

    private function shell(string $command, string $cwd): bool
    {
        $process = Process::fromShellCommandline($command, $cwd, null, null, 900);
        $process->run(fn ($type, $buffer) => $this->output->isVerbose() ? $this->output->write($buffer) : null);
        if (! $process->isSuccessful()) {
            $this->error("Comando fallito: $command\n".$process->getErrorOutput());
        }

        return $process->isSuccessful();
    }

    /** Dentro vendor/ non servono cartelle git, test, documentazione ed esempi dei pacchetti. */
    private function isDevOnly(string $relative): bool
    {
        return str_starts_with($relative, 'chianucci_app/vendor/')
            && (bool) preg_match('#/(\.git|\.github|tests|Tests|docs|examples)/#', $relative);
    }

    private function zip(string $root, string $zipPath): void
    {
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root, \FilesystemIterator::SKIP_DOTS));
        foreach ($files as $file) {
            if ($file->isFile() && ! $this->isDevOnly(substr($file->getPathname(), strlen($root) + 1))) {
                $zip->addFile($file->getPathname(), str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1)));
            }
        }
        $zip->close();
    }
}
