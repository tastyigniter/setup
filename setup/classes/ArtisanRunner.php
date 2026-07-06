<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;

class ArtisanRunner
{
    protected string $baseDirectory;

    protected $logger = null;

    protected bool $bootstrapped = false;

    public function __construct(string $baseDirectory, $logger = null)
    {
        $this->baseDirectory = rtrim($baseDirectory, '/');
        $this->logger = $logger;
    }

    public function run(string $command, array $parameters = []): int
    {
        $this->bootstrap();

        $exitCode = Artisan::call($command, $parameters);
        $output = trim(Artisan::output());

        if ($output !== '') {
            $this->log("Artisan {$command} output:%s%s", PHP_EOL, $output);
        }

        if ($exitCode !== 0) {
            throw new SetupException(sprintf(
                'Command "%s" failed with exit code %d.%s',
                $command,
                $exitCode,
                $output ? ' '.$output : ''
            ));
        }

        return $exitCode;
    }

    protected function bootstrap(): void
    {
        if ($this->bootstrapped) {
            return;
        }

        $autoload = $this->baseDirectory.'/vendor/autoload.php';
        $appFile = $this->baseDirectory.'/bootstrap/app.php';

        if (!is_file($autoload)) {
            throw new SetupException('Vendor autoload file was not found.');
        }

        if (!is_file($appFile)) {
            throw new SetupException('Application bootstrap file was not found.');
        }

        chdir($this->baseDirectory);

        $tempDirectory = $this->ensureWritableRuntimeDirectories();
        $this->configureTempDirectory($tempDirectory);

        require_once $autoload;

        $app = require $appFile;
        $app->make(Kernel::class)->bootstrap();

        $this->bootstrapped = true;
        $this->log('Laravel application bootstrapped from %s', $this->baseDirectory);
    }

    protected function ensureWritableRuntimeDirectories(): string
    {
        $directories = [
            $this->baseDirectory.'/bootstrap/cache',
            $this->baseDirectory.'/storage/framework/cache/data',
            $this->baseDirectory.'/storage/framework/sessions',
            $this->baseDirectory.'/storage/framework/views',
            $this->baseDirectory.'/storage/logs',
        ];

        foreach ($directories as $directory) {
            if (!is_dir($directory) && !@mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new SetupException('Unable to create directory: '.$directory);
            }

            if (!is_writable($directory)) {
                throw new SetupException('Directory is not writable: '.$directory);
            }
        }

        return $this->baseDirectory.'/bootstrap/cache';
    }

    protected function configureTempDirectory(string $directory): void
    {
        $directory = realpath($directory) ?: $directory;

        putenv('TMPDIR='.$directory);
        putenv('TEMP='.$directory);
        putenv('TMP='.$directory);
    }

    protected function log(string $message, ...$args): void
    {
        if ($this->logger) {
            ($this->logger)(vsprintf($message, $args));
        }
    }
}
