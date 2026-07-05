<?php

class SetupController
{
    public $page;

    public $logFile;

    protected string $baseDirectory;

    protected string $tempDirectory;

    protected SetupRepository $repository;

    protected RequirementChecker $requirements;

    public function __construct()
    {
        @set_time_limit(0);

        $this->baseDirectory = BASEPATH;
        $this->tempDirectory = SETUPPATH.'/temp';
        $this->logFile = SETUPPATH.'/setup.log';
        $this->writePostToLog();

        $this->repository = new SetupRepository(SETUPPATH.'/setup_config');
        $this->requirements = new RequirementChecker(SETUPPATH, $this->baseDirectory);

        $this->execPostHandler();
    }

    public function getPage()
    {
        if (!$this->page) {
            $this->page = new stdClass;
        }

        $allowed = ['start', 'database', 'install', 'complete'];
        $this->page->currentStep = 'start';

        if (isset($_GET['step']) && in_array($_GET['step'], $allowed, true)) {
            $this->page->currentStep = $_GET['step'];
        }

        return $this->page;
    }

    public function onCheckRequirements()
    {
        $code = (string)$this->post('code');
        $this->writeLog('System check: %s', $code);

        $result = $this->requirements->check($code);
        $blocking = $this->requirements->isBlocking($code);

        if ($result) {
            $passed = array_filter(explode(',', (string)$this->repository->get('requirements_passed', '')));
            if (!in_array($code, $passed, true)) {
                $passed[] = $code;
            }
            $this->repository->set('requirements_passed', implode(',', $passed));
        } elseif ($blocking) {
            $this->repository->set('requirements_passed', '');
        }

        $this->repository->save();
        $this->writeLog('Requirement %s %s', $code, $result ? '+OK' : '=FAIL');

        return [
            'result' => $result,
            'blocking' => $blocking,
        ];
    }

    public function onAcceptLicense()
    {
        if ($this->post('license_agreed') !== 'accepted') {
            throw new SetupException(lang('alert_license_error'));
        }

        if (!$this->requirementsComplete()) {
            throw new SetupException(lang('alert_requirement_error'));
        }

        $this->repository
            ->set('license_agreed', 'accepted')
            ->set('requirement', 'complete')
            ->save();

        $this->writeLog('License accepted');

        return ['step' => 'database'];
    }

    public function onValidateDatabase()
    {
        $this->confirmRequirements();

        if (!strlen($this->post('host'))) {
            throw new SetupException('Please specify a database host');
        }

        if (!strlen($database = $this->post('database'))) {
            throw new SetupException('Please specify the database name');
        }

        $config = $this->verifyDbConfiguration($this->post());
        $db = $this->testDbConnection($config);

        if (!$db->isFreshlyInstalled() && !$db->canUpgradeExistingInstallation()) {
            throw new SetupException(sprintf(
                lang('alert_database_in_use'),
                $this->e($db->config('database'))
            ));
        }

        $this->repository->set('database', $config)->save();
        $this->writeLog('Database %s +OK', $database);

        return ['step' => 'install'];
    }

    public function onInstall()
    {
        $this->confirmRequirements();

        if (!$this->repository->get('database')) {
            throw new SetupException('Database configuration is missing.');
        }

        @set_time_limit(0);

        $process = (string)$this->post('process');
        $this->writeLog('Install step: %s', $process);

        $result = match ($process) {
            'prepare' => $this->installPrepare(),
            'download' => $this->installDownload(),
            'extract' => $this->installExtract(),
            'config' => $this->installConfig(),
            'install' => $this->installApplication(),
            'finalize' => $this->installFinalize(),
            default => throw new SetupException('Unknown install step.'),
        };

        $this->repository->set('install', $process)->save();
        $this->writeLog('Install step %s +OK', $process);

        return ['result' => $result];
    }

    protected function installPrepare(): bool
    {
        if (!is_dir($this->tempDirectory) && !mkdir($this->tempDirectory, 0755, true) && !is_dir($this->tempDirectory)) {
            throw new SetupException('Unable to create temporary directory.');
        }

        if (!is_writable($this->baseDirectory)) {
            throw new SetupException('Installation directory is not writable.');
        }

        return true;
    }

    protected function installDownload(): array
    {
        $offset = (int)$this->post('offset', 0);
        $downloader = new ReleaseDownloader(
            $this->tempDirectory,
            fn (...$args) => $this->writeLog(...$args),
            $this->getPinnedVersion(),
            $this->getCachedReleaseInfo()
        );

        $progress = $downloader->download($offset);
        $release = $downloader->getReleaseInfo();

        $this->repository
            ->set('release_tag', $progress['tag'])
            ->set('release_version', $progress['version'])
            ->set('release_download_url', $release['asset']['url'])
            ->set('release_asset_name', $release['asset']['name'])
            ->set('release_asset_size', $release['asset']['size'])
            ->save();

        unset($progress['asset']);

        return $progress;
    }

    protected function installExtract(): bool
    {
        $version = $this->getPinnedVersion();
        $downloader = new ReleaseDownloader(
            $this->tempDirectory,
            fn (...$args) => $this->writeLog(...$args),
            $version
        );

        $downloader->extract($this->baseDirectory);

        return true;
    }

    protected function installConfig(): bool
    {
        $database = $this->repository->get('database', []);
        $writer = new EnvWriter($this->baseDirectory);
        $writer->write($database, $this->getBaseUrl());

        $runner = new ArtisanRunner($this->baseDirectory, fn (...$args) => $this->writeLog(...$args));
        $runner->run('key:generate', ['--force' => true]);

        return true;
    }

    protected function installApplication(): bool
    {
        $runner = new ArtisanRunner($this->baseDirectory, fn (...$args) => $this->writeLog(...$args));
        $runner->run('igniter:install', ['--force' => true, '--no-interaction' => true]);

        return true;
    }

    protected function installFinalize(): array
    {
        $runner = new ArtisanRunner($this->baseDirectory, fn (...$args) => $this->writeLog(...$args));

        try {
            $runner->run('storage:link', ['--force' => true]);
        } catch (SetupException $ex) {
            $this->writeLog('storage:link skipped: %s', $ex->getMessage());
        }

        $this->writeRootHtaccess();
        $this->cleanUpAfterInstall();

        return [
            'adminUrl' => rtrim($this->getBaseUrl(), '/').'/admin',
            'frontUrl' => rtrim($this->getBaseUrl(), '/').'/',
        ];
    }

    protected function writeRootHtaccess(): void
    {
        $htaccess = $this->baseDirectory.'/.htaccess';

        if (is_file($htaccess)) {
            return;
        }

        $contents = <<<'HTACCESS'
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
HTACCESS;

        file_put_contents($htaccess, $contents);
        $this->writeLog('Generated root .htaccess redirect to public/');
    }

    protected function cleanUpAfterInstall(): void
    {
        if (is_dir($this->tempDirectory)) {
            $this->deleteDirectory($this->tempDirectory);
        }
    }

    protected function execPostHandler()
    {
        $handler = $this->post('handler');
        if ($handler === null) {
            return;
        }

        if ($handler === '') {
            exit;
        }

        try {
            if (!preg_match('/^on[A-Z][\w]*$/', $handler)) {
                throw new SetupException(sprintf('Invalid handler: %s', $this->e($handler)));
            }

            if (method_exists($this, $handler) && ($result = $this->$handler()) !== null) {
                $this->writeLog('Execute handler (%s): %s', $handler, print_r($result, true));
                header('Content-Type: application/json');
                exit(json_encode($result));
            }
        } catch (Exception $ex) {
            header($_SERVER['SERVER_PROTOCOL'].' 500 Internal Server Error', true, 500);
            $this->writeLog('Handler error (%s): %s', $handler, $ex->getMessage());
            $this->writeLog(['Trace log:', '%s'], $ex->getTraceAsString());
            exit($ex->getMessage());
        }

        exit;
    }

    protected function testDbConnection(array $config = [])
    {
        try {
            $db = SetupPDO::makeFromConfig($config);

            if (!$db->compareInstalledVersion()) {
                throw new SetupException(lang('text_mysql_version'));
            }
        } catch (PDOException $ex) {
            throw new SetupException('Connection failed: '.$ex->getMessage());
        }

        return $db;
    }

    protected function verifyDbConfiguration($config): array
    {
        $result = [
            'host' => '127.0.0.1',
            'port' => 3306,
            'database' => '',
            'username' => '',
            'password' => '',
            'prefix' => 'ti_',
        ];

        foreach (['host', 'port', 'database', 'username', 'password', 'prefix'] as $key) {
            if (isset($config[$key]) && is_string($config[$key])) {
                $result[$key] = $key === 'password' ? $config[$key] : trim($config[$key]);
            }
        }

        return $result;
    }

    protected function confirmRequirements(): void
    {
        if ($this->repository->get('license_agreed') !== 'accepted') {
            throw new SetupException(lang('alert_license_error'));
        }

        if (!$this->requirementsComplete()) {
            throw new SetupException(lang('alert_requirement_error'));
        }
    }

    protected function requirementsComplete(): bool
    {
        $passed = array_filter(explode(',', (string)$this->repository->get('requirements_passed', '')));

        foreach (RequirementChecker::definitions() as $code => $definition) {
            if (!empty($definition['warning'])) {
                continue;
            }

            if (!in_array($code, $passed, true) || !$this->requirements->check($code)) {
                return false;
            }
        }

        return true;
    }

    protected function getCachedReleaseInfo(): ?array
    {
        $url = $this->repository->get('release_download_url');
        if (!$url) {
            return null;
        }

        return [
            'tag' => $this->repository->get('release_tag'),
            'version' => $this->repository->get('release_version'),
            'asset' => [
                'name' => $this->repository->get('release_asset_name', ''),
                'url' => $url,
                'size' => (int)$this->repository->get('release_asset_size', 0),
            ],
        ];
    }

    protected function getPinnedVersion(): ?string
    {
        if (isset($_GET['version']) && strlen($_GET['version'])) {
            return trim($_GET['version']);
        }

        return null;
    }

    public function getDatabaseDetails()
    {
        $defaults = [
            'database' => '',
            'host' => '127.0.0.1',
            'port' => 3306,
            'username' => '',
            'password' => '',
            'prefix' => 'ti_',
        ];

        $settings = [];
        $db = $this->repository->get('database', []);

        foreach ($defaults as $item => $value) {
            if ($this->post($item)) {
                $settings[$item] = $this->post($item);
            } elseif (isset($db[$item])) {
                $settings[$item] = $db[$item];
            } else {
                $settings[$item] = $value;
            }
        }

        return (object)$settings;
    }

    protected function getBaseUrl(): string
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return 'http://localhost/';
        }

        $scheme = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') ? 'https' : 'http';
        $path = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

        return $scheme.'://'.$_SERVER['HTTP_HOST'].$path;
    }

    protected function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($directory);
    }

    protected function e($value): string
    {
        return htmlentities((string)$value, ENT_QUOTES, 'UTF-8', false);
    }

    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }

        if (array_key_exists($key, $_POST)) {
            $result = $_POST[$key];

            return is_string($result) ? trim($result) : $result;
        }

        return $default;
    }

    public function cleanLog(): void
    {
        $message = '.++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++.'."\n";
        $message .= '.------------------- TASTYIGNITER SETUP LOG ------------------.'."\n";
        $message .= '.++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++.';
        file_put_contents($this->logFile, $message.PHP_EOL);
    }

    public function writeLog(): void
    {
        $args = func_get_args();
        $message = array_shift($args);

        if (is_array($message)) {
            $message = implode(PHP_EOL, $message);
        }

        $date = '';
        if (!array_key_exists('hideTime', $args)) {
            $date = '['.date('Y/m/d H:i:s').'] => ';
        }

        file_put_contents($this->logFile, vsprintf($date.$message.PHP_EOL, $args), FILE_APPEND);
    }

    protected function writePostToLog(): void
    {
        if (!isset($_POST) || !count($_POST)) {
            return;
        }

        $postData = $_POST;
        if (array_key_exists('disableLog', $postData)) {
            $postData = ['disableLog' => true];
        }

        foreach (['password', 'confirm_password'] as $field) {
            if (isset($postData[$field])) {
                $postData[$field] = '*******';
            }
        }

        $this->writeLog('.============================ POST REQUEST ==========================.', ['hideTime' => true]);
        $this->writeLog('Postback payload: %s', print_r($postData, true));
    }
}
