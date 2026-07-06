<?php

class EnvWriter
{
    protected string $baseDirectory;

    public function __construct(string $baseDirectory)
    {
        $this->baseDirectory = rtrim($baseDirectory, '/');
    }

    public function write(array $database, string $appUrl, string $appName = 'TastyIgniter'): void
    {
        $example = $this->baseDirectory.'/.env.example';
        $env = $this->baseDirectory.'/.env';

        if (!is_file($example)) {
            throw new SetupException('.env.example was not found in the release package.');
        }

        if (is_file($env)) {
            @unlink($env);
        }

        copy($example, $env);

        $replacements = [
            'APP_NAME=' => 'APP_NAME="'.addslashes($appName).'"',
            'APP_URL=' => 'APP_URL='.rtrim($appUrl, '/'),
            'DB_HOST=' => 'DB_HOST='.$database['host'],
            'DB_PORT=' => 'DB_PORT='.$database['port'],
            'DB_DATABASE=' => 'DB_DATABASE='.$database['database'],
            'DB_USERNAME=' => 'DB_USERNAME='.$database['username'],
            'DB_PASSWORD=' => 'DB_PASSWORD="'.$this->escapeEnvValue($database['password']).'"',
            'DB_PREFIX=' => 'DB_PREFIX='.$database['prefix'],
        ];

        foreach ($replacements as $search => $replace) {
            $this->replaceLine($env, $search, $replace);
        }
    }

    protected function replaceLine(string $file, string $search, string $replace): void
    {
        $contents = file_get_contents($file);
        $pattern = '/^'.preg_quote($search, '/').'.*$/m';

        if (preg_match($pattern, $contents)) {
            $contents = preg_replace($pattern, $replace, $contents);
        } else {
            $contents .= PHP_EOL.$replace;
        }

        file_put_contents($file, $contents);
    }

    protected function escapeEnvValue(string $value): string
    {
        return str_replace('"', '\\"', $value);
    }
}
