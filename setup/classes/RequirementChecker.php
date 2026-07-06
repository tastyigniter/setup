<?php

class RequirementChecker
{
    public const DOCS_TROUBLESHOOTING = 'https://tastyigniter.com/docs/installation#troubleshooting';

    protected string $setupPath;

    protected string $basePath;

    protected string $tempPath;

    public function __construct(string $setupPath, string $basePath)
    {
        $this->setupPath = $setupPath;
        $this->basePath = $basePath;
        $this->tempPath = $setupPath.'/temp';
    }

    public static function definitions(): array
    {
        return [
            'php' => [
                'label' => 'label_php_version',
                'hint' => 'hint_php_version',
            ],
            'extensions' => [
                'label' => 'label_extensions',
                'hint' => 'hint_extensions',
            ],
            'pdo' => [
                'label' => 'label_pdo',
                'hint' => 'hint_pdo',
            ],
            'curl' => [
                'label' => 'label_curl',
                'hint' => 'hint_curl',
            ],
            'zip' => [
                'label' => 'label_zip',
                'hint' => 'hint_zip',
            ],
            'network' => [
                'label' => 'label_network',
                'hint' => 'hint_network',
            ],
            'writable' => [
                'label' => 'label_writable',
                'hint' => 'hint_writable',
            ],
            'memory_limit' => [
                'label' => 'label_memory_limit',
                'hint' => 'hint_memory_limit',
                'warning' => true,
            ],
            'max_execution_time' => [
                'label' => 'label_max_execution_time',
                'hint' => 'hint_max_execution_time',
                'warning' => true,
            ],
        ];
    }

    public function check(string $code): bool
    {
        return match ($code) {
            'php' => version_compare(PHP_VERSION, TI_PHP_VERSION, '>='),
            'extensions' => $this->checkExtensions(),
            'pdo' => extension_loaded('pdo') && extension_loaded('pdo_mysql'),
            'curl' => function_exists('curl_init') && defined('CURLOPT_FOLLOWLOCATION'),
            'zip' => class_exists('ZipArchive'),
            'network' => $this->checkNetwork(),
            'writable' => $this->checkWritable(),
            'memory_limit' => $this->checkMemoryLimit(),
            'max_execution_time' => $this->checkMaxExecutionTime(),
            default => false,
        };
    }

    public function isBlocking(string $code): bool
    {
        $definition = self::definitions()[$code] ?? [];

        return empty($definition['warning']);
    }

    protected function checkExtensions(): bool
    {
        foreach (['bcmath', 'ctype', 'dom', 'exif', 'gd', 'intl', 'json', 'mbstring', 'openssl', 'tokenizer', 'xml'] as $extension) {
            if (!extension_loaded($extension)) {
                return false;
            }
        }

        return true;
    }

    protected function checkNetwork(): bool
    {
        if (!function_exists('curl_init')) {
            return false;
        }

        $curl = curl_init('https://github.com/tastyigniter/TastyIgniter/releases/latest');
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_USERAGENT => 'TastyIgniter-Setup-Wizard',
            CURLOPT_NOBODY => true,
        ]);
        curl_exec($curl);
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $code >= 200 && $code < 400;
    }

    protected function checkWritable(): bool
    {
        if (!is_writable($this->setupPath) || !is_writable($this->basePath)) {
            return false;
        }

        if (!is_dir($this->tempPath) && !@mkdir($this->tempPath, 0755, true) && !is_dir($this->tempPath)) {
            return false;
        }

        $probe = $this->tempPath.'/.write-test';
        $written = @file_put_contents($probe, 'test') !== false;

        if ($written) {
            @unlink($probe);
        }

        return $written;
    }

    protected function checkMemoryLimit(): bool
    {
        $limit = ini_get('memory_limit');
        if ($limit === '-1') {
            return true;
        }

        return $this->parseIniSize($limit) >= 256 * 1024 * 1024;
    }

    protected function checkMaxExecutionTime(): bool
    {
        $limit = (int)ini_get('max_execution_time');

        return $limit === 0 || $limit >= 120;
    }

    protected function parseIniSize(string $value): int
    {
        $value = trim($value);
        $unit = strtolower(substr($value, -1));
        $number = (int)$value;

        return match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => (int)$value,
        };
    }
}
