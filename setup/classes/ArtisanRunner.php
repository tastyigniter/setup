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

        require_once $autoload;

        $app = require $appFile;
        $app->make(Kernel::class)->bootstrap();

        $this->bootstrapped = true;
        $this->log('Laravel application bootstrapped from %s', $this->baseDirectory);
    }

    protected function log(string $message, ...$args): void
    {
        if ($this->logger) {
            ($this->logger)(vsprintf($message, $args));
        }
    }
}
