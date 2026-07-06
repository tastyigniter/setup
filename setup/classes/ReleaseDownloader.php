<?php

class ReleaseDownloader
{
    public const REPO = 'tastyigniter/TastyIgniter';

    public const RELEASE_BASE = 'https://github.com/'.self::REPO.'/releases';

    protected string $tempDirectory;

    protected $logger = null;

    protected ?string $pinnedVersion;

    protected ?array $cachedRelease = null;

    public function __construct(string $tempDirectory, $logger = null, ?string $pinnedVersion = null, ?array $cachedRelease = null)
    {
        $this->tempDirectory = rtrim($tempDirectory, '/');
        $this->logger = $logger;
        $this->pinnedVersion = $pinnedVersion;
        $this->cachedRelease = $cachedRelease;
    }

    public function getReleaseInfo(): array
    {
        if ($this->cachedRelease) {
            return $this->cachedRelease;
        }

        if ($this->pinnedVersion) {
            $tag = str_starts_with($this->pinnedVersion, 'v') ? $this->pinnedVersion : 'v'.$this->pinnedVersion;
        } else {
            $tag = $this->resolveLatestTag();
        }

        $version = ltrim($tag, 'v');
        $asset = $this->resolveReleaseAsset($tag, $version);

        $this->cachedRelease = [
            'tag' => $tag,
            'version' => $version,
            'asset' => $asset,
        ];

        return $this->cachedRelease;
    }

    public function getArchivePath(): string
    {
        return $this->tempDirectory.'/tastyigniter-release.zip';
    }

    public function download(?int $offset = 0): array
    {
        if (!is_dir($this->tempDirectory) && !mkdir($this->tempDirectory, 0755, true) && !is_dir($this->tempDirectory)) {
            throw new SetupException('Unable to create temporary download directory.');
        }

        $release = $this->getReleaseInfo();
        $asset = $release['asset'];
        $destination = $this->getArchivePath();

        if ($this->hasCompleteArchive($release)) {
            $downloaded = filesize($destination) ?: 0;
            $this->log('Using existing release archive (%s bytes)', $downloaded);

            return [
                'complete' => true,
                'downloaded' => $downloaded,
                'total' => (int)($asset['size'] ?? $downloaded),
                'tag' => $release['tag'],
                'version' => $release['version'],
                'asset' => $asset,
                'reused' => true,
            ];
        }

        if ($offset === 0 && is_file($destination)) {
            $existing = filesize($destination) ?: 0;
            $expected = (int)($asset['size'] ?? 0);

            if ($existing > 0 && ($expected === 0 || $existing < $expected)) {
                $offset = $existing;
            }
        }

        $mode = $offset > 0 && file_exists($destination) ? 'ab' : 'wb';

        if ($mode === 'wb' && file_exists($destination)) {
            @unlink($destination);
        }

        $handle = fopen($destination, $mode);
        if ($handle === false) {
            throw new SetupException('Unable to open temporary file for download.');
        }

        $headers = [
            'User-Agent: TastyIgniter-Setup-Wizard',
        ];

        if ($offset > 0) {
            $headers[] = 'Range: bytes='.$offset.'-';
        }

        $curl = curl_init($asset['url']);
        curl_setopt_array($curl, [
            CURLOPT_FILE => $handle,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_HTTPHEADER => $headers,
        ]);

        curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        fclose($handle);

        if (!in_array($httpCode, [200, 206], true)) {
            throw new SetupException('Failed to download TastyIgniter release (HTTP '.$httpCode.').');
        }

        $downloaded = filesize($destination) ?: 0;
        $total = (int)($asset['size'] ?? 0);
        $complete = !($total > 0) || $downloaded >= $total;

        $this->log('Downloaded %s bytes of release %s', $downloaded, $release['tag']);

        return [
            'complete' => $complete,
            'downloaded' => $downloaded,
            'total' => $total,
            'tag' => $release['tag'],
            'version' => $release['version'],
            'asset' => $asset,
        ];
    }

    public function extract(string $baseDirectory): string
    {
        $archive = $this->getArchivePath();
        if (!is_file($archive)) {
            throw new SetupException('Release archive is missing.');
        }

        $extractRoot = $this->tempDirectory.'/extract';
        $this->deleteDirectory($extractRoot);
        mkdir($extractRoot, 0755, true);

        $zip = new ZipArchive();
        if ($zip->open($archive) !== true) {
            throw new SetupException('Unable to open the downloaded release archive.');
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);
            if ($name === false || str_ends_with($name, '/')) {
                continue;
            }

            $zip->extractTo($extractRoot, [$name]);
        }

        $zip->close();

        $source = $this->resolveExtractedRoot($extractRoot);
        $this->copyDirectory($source, rtrim($baseDirectory, '/'));

        $this->log('Extracted release from %s to %s', $source, $baseDirectory);

        return $source;
    }

    protected function resolveLatestTag(): string
    {
        $finalUrl = $this->followRedirects(self::RELEASE_BASE.'/latest');
        $tag = $this->extractTagFromUrl($finalUrl);

        if ($tag) {
            return $tag;
        }

        throw new SetupException(sprintf(
            'Unable to resolve the latest TastyIgniter release from %s.',
            $finalUrl
        ));
    }

    protected function extractTagFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH) ?: $url;

        if (preg_match('~/releases/tag/(v[^/?]+)~i', $path, $matches)) {
            return $matches[1];
        }

        return null;
    }

    protected function hasCompleteArchive(array $release): bool
    {
        $path = $this->getArchivePath();

        if (!is_file($path)) {
            return false;
        }

        $size = filesize($path) ?: 0;
        if ($size <= 0) {
            return false;
        }

        $expected = (int)($release['asset']['size'] ?? 0);

        if ($expected > 0 && $size < $expected) {
            return false;
        }

        return $this->isValidZip($path);
    }

    protected function isValidZip(string $path): bool
    {
        if (!class_exists('ZipArchive')) {
            return true;
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return false;
        }

        $valid = $zip->numFiles > 0;
        $zip->close();

        return $valid;
    }

    protected function resolveReleaseAsset(string $tag, string $version): array
    {
        $candidates = [
            sprintf('tastyigniter-%s.zip', $version),
            sprintf('tastyigniter-%s.zip', $tag),
        ];

        foreach (array_unique($candidates) as $assetName) {
            $url = sprintf('%s/download/%s/%s', self::RELEASE_BASE, rawurlencode($tag), rawurlencode($assetName));
            $meta = $this->fetchUrlMeta($url);

            if (in_array($meta['code'], [200, 206], true)) {
                return [
                    'name' => $assetName,
                    'url' => $meta['url'] ?: $url,
                    'size' => $meta['size'],
                ];
            }
        }

        $discovered = $this->discoverReleaseAsset($tag);
        if ($discovered) {
            return $discovered;
        }

        throw new SetupException(sprintf(
            'Distribution release with bundled dependencies not found for %s. Please try again later.',
            $tag
        ));
    }

    protected function discoverReleaseAsset(string $tag): ?array
    {
        $pageUrl = self::RELEASE_BASE.'/tag/'.rawurlencode($tag);
        $html = $this->requestBody($pageUrl);

        if ($html === '') {
            return null;
        }

        $pattern = '#href="(/'.preg_quote(self::REPO, '#').'/releases/download/[^"]+/tastyigniter-[^"]+\.zip)"#i';
        if (!preg_match($pattern, $html, $matches)) {
            return null;
        }

        $url = 'https://github.com'.$matches[1];
        $meta = $this->fetchUrlMeta($url);

        if (!in_array($meta['code'], [200, 206], true)) {
            return null;
        }

        return [
            'name' => basename(parse_url($url, PHP_URL_PATH) ?: ''),
            'url' => $meta['url'] ?: $url,
            'size' => $meta['size'],
        ];
    }

    protected function followRedirects(string $url): string
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: TastyIgniter-Setup-Wizard',
            ],
        ]);

        curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $finalUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) ?: $url;
        curl_close($curl);

        if ($httpCode < 200 || $httpCode >= 400) {
            throw new SetupException('Unable to reach GitHub Releases (HTTP '.$httpCode.').');
        }

        return $finalUrl;
    }

    protected function fetchUrlMeta(string $url): array
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: TastyIgniter-Setup-Wizard',
            ],
        ]);

        curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $size = (int)curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        $finalUrl = curl_getinfo($curl, CURLINFO_EFFECTIVE_URL) ?: $url;
        curl_close($curl);

        return [
            'code' => $httpCode,
            'size' => $size > 0 ? $size : 0,
            'url' => $finalUrl,
        ];
    }

    protected function requestBody(string $url): string
    {
        $curl = curl_init($url);
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'User-Agent: TastyIgniter-Setup-Wizard',
            ],
        ]);

        $body = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode < 200 || $httpCode >= 400) {
            return '';
        }

        return is_string($body) ? $body : '';
    }

    protected function resolveExtractedRoot(string $extractRoot): string
    {
        $entries = array_values(array_filter(scandir($extractRoot) ?: [], fn ($entry) => !in_array($entry, ['.', '..'], true)));
        if (count($entries) === 1 && is_dir($extractRoot.'/'.$entries[0])) {
            return $extractRoot.'/'.$entries[0];
        }

        return $extractRoot;
    }

    protected function copyDirectory(string $source, string $destination): void
    {
        if (!is_dir($destination) && !mkdir($destination, 0755, true) && !is_dir($destination)) {
            throw new SetupException('Unable to create destination directory.');
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destination.DIRECTORY_SEPARATOR.$iterator->getSubPathName();

            if ($item->isDir()) {
                if (!is_dir($target) && !mkdir($target, 0755, true) && !is_dir($target)) {
                    throw new SetupException('Unable to create directory: '.$target);
                }

                continue;
            }

            if (!is_dir(dirname($target)) && !mkdir(dirname($target), 0755, true) && !is_dir(dirname($target))) {
                throw new SetupException('Unable to create directory: '.dirname($target));
            }

            if (!copy($item->getPathname(), $target)) {
                throw new SetupException('Unable to copy file: '.$item->getPathname());
            }
        }
    }

    protected function deleteDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS | FilesystemIterator::UNIX_PATHS),
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

    protected function log(string $message, ...$args): void
    {
        if ($this->logger) {
            ($this->logger)(vsprintf($message, $args));
        }
    }
}
