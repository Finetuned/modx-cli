<?php

namespace MODX\CLI\Command;

use MODX\CLI\Output\StreamingOutputTrait;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

/**
 * Self-update the MODX CLI Phar
 */
class SelfUpdate extends BaseCmd
{
    use StreamingOutputTrait;

    protected $name = 'self-update';
    protected $description = 'Update MODX CLI to the latest release';
    protected $help = 'Self-update MODX CLI when installed as a Phar. Composer installs should be updated via Composer.';

    private const API_BASE = 'https://api.github.com/repos/Finetuned/modx-cli';

    public function __construct()
    {
        parent::__construct();
        $this->setAliases(['update']);
    }

    /**
     * @inheritDoc
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'stable',
                null,
                InputOption::VALUE_NONE,
                'Update to latest stable release (default)'
            ),
            array(
                'nightly',
                null,
                InputOption::VALUE_NONE,
                'Update to latest pre-release (alpha/beta)'
            ),
            array(
                'target-version',
                null,
                InputOption::VALUE_REQUIRED,
                'Update to a specific version (e.g. 0.6.1)'
            ),
            array(
                'to',
                't',
                InputOption::VALUE_REQUIRED,
                'Alias for --target-version'
            ),
            array(
                'force',
                null,
                InputOption::VALUE_NONE,
                'Update without confirmation'
            ),
            array(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Preview update without executing'
            ),
            array(
                'no-backup',
                null,
                InputOption::VALUE_NONE,
                'Skip creating a backup'
            ),
            array(
                'check',
                null,
                InputOption::VALUE_NONE,
                'Only check for available updates'
            ),
        ));
    }

    protected function process()
    {
        $json = (bool) $this->option('json');
        $currentVersion = $this->getCurrentVersion();

        if ($currentVersion === null) {
            return $this->fail('Unable to determine current version.', $json);
        }

        $channel = $this->resolveChannel();
        if ($channel === null) {
            return $this->fail('Choose only one of --stable or --nightly.', $json);
        }

        $targetVersion = $this->option('target-version');
        $toVersion = $this->option('to');
        if ($targetVersion !== null && $toVersion !== null && $targetVersion !== $toVersion) {
            return $this->fail('Use only one of --target-version or --to.', $json);
        }
        if ($targetVersion === null && $toVersion !== null) {
            $targetVersion = $toVersion;
        }
        if ($targetVersion !== null) {
            $targetVersion = $this->normalizeVersion($targetVersion);
        }

        $release = $this->resolveRelease($channel, $targetVersion, $currentVersion, $json);
        if ($release === null) {
            return 1;
        }

        $latestVersion = $release['version'];
        $isUpdateAvailable = version_compare($latestVersion, $currentVersion, '>');

        if ($this->option('check')) {
            return $this->handleCheck($json, $currentVersion, $release, $isUpdateAvailable);
        }

        if (!$this->isPharInstall()) {
            return $this->fail(
                'Self-update is only supported for Phar installs. Update with Composer: `composer global update finetuned/modx-cli`.',
                $json
            );
        }

        if (!$isUpdateAvailable && $targetVersion === null && !$this->option('nightly')) {
            return $this->handleNoUpdate($json, $currentVersion, $release);
        }

        if ($this->option('dry-run')) {
            return $this->handleDryRun($json, $currentVersion, $release);
        }

        $downloadUrl = $release['asset']['url'];

        if (!$this->option('force')) {
            $confirmMessage = sprintf(
                'You have version %s. Would you like to update to %s?',
                $currentVersion,
                $release['version']
            );
            if (!$this->confirm($confirmMessage)) {
                return 0;
            }
        }

        return $this->performUpdate($release, $currentVersion, $json, $downloadUrl);
    }

    private function handleCheck(bool $json, string $currentVersion, array $release, bool $isUpdateAvailable): int
    {
        if ($json) {
            $this->output->writeln(json_encode([
                'success' => true,
                'current_version' => $currentVersion,
                'latest_version' => $release['version'],
                'update_available' => $isUpdateAvailable,
                'download_url' => $release['asset']['url'],
                'release_notes' => $release['notes'],
                'file_size' => $release['asset']['size'],
            ], JSON_PRETTY_PRINT));
            return 0;
        }

        $this->info('Current version: ' . $currentVersion);
        $this->info('Latest version: ' . $release['version']);
        if ($isUpdateAvailable) {
            $this->comment('A new version is available!');
        } else {
            $this->comment('You are already on the latest version.');
        }
        $this->renderReleaseNotes($release['notes']);

        return 0;
    }

    private function handleNoUpdate(bool $json, string $currentVersion, array $release): int
    {
        if ($json) {
            $this->output->writeln(json_encode([
                'success' => true,
                'current_version' => $currentVersion,
                'latest_version' => $release['version'],
                'update_available' => false,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->comment('MODX CLI is already at the latest version.');
        }

        return 0;
    }

    private function handleDryRun(bool $json, string $currentVersion, array $release): int
    {
        if ($json) {
            $this->output->writeln(json_encode([
                'success' => true,
                'dry_run' => true,
                'current_version' => $currentVersion,
                'target_version' => $release['version'],
                'download_url' => $release['asset']['url'],
                'file_size' => $release['asset']['size'],
            ], JSON_PRETTY_PRINT));
            return 0;
        }

        $this->info('Current version: ' . $currentVersion);
        $this->info('Target version: ' . $release['version']);
        $this->comment('Would download: ' . $release['asset']['url']);
        $this->comment('File size: ' . $this->formatBytes($release['asset']['size']));
        $this->comment('(Dry run - no changes made)');

        return 0;
    }

    private function performUpdate(array $release, string $currentVersion, bool $json, string $downloadUrl): int
    {
        $pharPath = $this->getExecutablePath();
        $pharDir = dirname($pharPath);

        if (!is_writable($pharPath) || !is_writable($pharDir)) {
            return $this->fail("{$pharPath} is not writable by the current user.", $json);
        }

        $tempPath = $pharDir . '/.modx-cli-update-' . uniqid('', true) . '.phar';
        $backupPath = $this->buildBackupPath($pharPath, $currentVersion);

        if (!$json) {
            $this->comment('Downloading ' . $release['asset']['name'] . '...');
        }

        if (!$this->downloadFile($downloadUrl, $tempPath, $release['asset']['size'])) {
            return $this->fail('Download failed.', $json);
        }

        if (!$this->verifyChecksum($release, $tempPath, $json)) {
            @unlink($tempPath);
            return 1;
        }

        if (!$this->verifyPharRuns($tempPath, $json)) {
            @unlink($tempPath);
            return 1;
        }

        $backupCreated = false;
        if (!$this->option('no-backup')) {
            if (!copy($pharPath, $backupPath)) {
                @unlink($tempPath);
                return $this->fail('Failed to create backup before update.', $json);
            }
            $backupCreated = true;
        }

        $mode = fileperms($pharPath) & 511;
        if (!chmod($tempPath, $mode)) {
            $this->restoreBackup($backupCreated, $backupPath, $pharPath);
            return $this->fail('Unable to set permissions on the downloaded Phar.', $json);
        }

        if (!rename($tempPath, $pharPath)) {
            $this->restoreBackup($backupCreated, $backupPath, $pharPath);
            return $this->fail('Unable to replace the existing Phar.', $json);
        }

        if ($backupCreated) {
            @unlink($backupPath);
        }

        if ($json) {
            $this->output->writeln(json_encode([
                'success' => true,
                'current_version' => $currentVersion,
                'updated_version' => $release['version'],
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info(sprintf(
                'Update complete. %s → %s',
                $currentVersion,
                $release['version']
            ));
        }

        return 0;
    }

    private function resolveChannel(): ?string
    {
        $stable = (bool) $this->option('stable');
        $nightly = (bool) $this->option('nightly');

        if ($stable && $nightly) {
            return null;
        }

        if ($nightly) {
            return 'nightly';
        }

        return 'stable';
    }

    private function resolveRelease(string $channel, ?string $targetVersion, string $currentVersion, bool $json): ?array
    {
        if ($targetVersion !== null) {
            $errorMessage = null;
            $release = $this->fetchReleaseByTag($targetVersion, $errorMessage);
            if ($release === null) {
                $this->fail(
                    $errorMessage ?? "No release found for version {$targetVersion}.",
                    $json
                );
                return null;
            }
            return $release;
        }

        $releases = $this->fetchReleases($json);
        if ($releases === null) {
            return null;
        }

        $includePrerelease = $channel === 'nightly';
        $release = $this->selectLatestRelease($releases, $includePrerelease);

        if ($release === null) {
            $this->fail(
                sprintf(
                    'No releases found for channel "%s". Current version: %s.',
                    $channel,
                    $currentVersion
                ),
                $json
            );
            return null;
        }

        return $release;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function fetchReleases(bool $json): ?array
    {
        $url = self::API_BASE . '/releases?per_page=100';
        $response = $this->httpGet($url);

        if ($response['status'] !== 200) {
            $this->fail('Failed to fetch releases from GitHub.', $json);
            return null;
        }

        $data = json_decode($response['body'], true);
        if (!is_array($data)) {
            $this->fail('Invalid response from GitHub releases API.', $json);
            return null;
        }

        return $data;
    }

    private function fetchReleaseByTag(string $version, ?string &$errorMessage = null): ?array
    {
        $tag = $version;
        if (strpos($tag, 'v') !== 0) {
            $tag = 'v' . $tag;
        }

        $response = $this->httpGet(self::API_BASE . '/releases/tags/' . $tag);
        if ($response['status'] === 404 && $tag[0] === 'v') {
            $response = $this->httpGet(self::API_BASE . '/releases/tags/' . $version);
        }

        if ($response['status'] !== 200) {
            if ($response['status'] === 404) {
                $tagName = $tag;
                if ($this->tagExists($tagName) || $this->tagExists($version)) {
                    $errorMessage = sprintf(
                        'Tag %s exists but no GitHub release was found. Publish a release to enable self-update.',
                        $tagName
                    );
                } else {
                    $errorMessage = sprintf('No release found for version %s.', $version);
                }
            } else {
                $errorMessage = sprintf(
                    'Failed to fetch release for version %s (HTTP %d).',
                    $version,
                    $response['status']
                );
            }
            return null;
        }

        $release = json_decode($response['body'], true);
        if (!is_array($release)) {
            $errorMessage = sprintf('Invalid release data for version %s.', $version);
            return null;
        }

        $normalized = $this->normalizeRelease($release);
        if ($normalized === null) {
            if (empty($release['assets'])) {
                $errorMessage = sprintf(
                    'Release %s has no compiled assets. A .phar asset is required for self-update.',
                    $tag
                );
            } else {
                $errorMessage = sprintf(
                    'Release %s does not include a .phar asset.',
                    $tag
                );
            }
        }

        return $normalized;
    }

    private function tagExists(string $tag): bool
    {
        if ($tag === '') {
            return false;
        }

        $response = $this->httpGet(self::API_BASE . '/git/ref/tags/' . $tag);
        return $response['status'] === 200;
    }

    /**
     * @param array<int, array<string, mixed>> $releases
     */
    private function selectLatestRelease(array $releases, bool $includePrerelease): ?array
    {
        $latest = null;
        foreach ($releases as $release) {
            if (!is_array($release)) {
                continue;
            }
            if (!$includePrerelease && !empty($release['prerelease'])) {
                continue;
            }

            $normalized = $this->normalizeRelease($release);
            if ($normalized === null) {
                continue;
            }

            if ($latest === null || version_compare($normalized['version'], $latest['version'], '>')) {
                $latest = $normalized;
            }
        }

        if ($includePrerelease && $latest === null) {
            return null;
        }

        return $latest;
    }

    private function normalizeRelease(array $release): ?array
    {
        if (empty($release['tag_name']) || empty($release['assets'])) {
            return null;
        }

        $version = $this->normalizeVersion($release['tag_name']);
        $asset = $this->findPharAsset($release['assets']);
        if ($asset === null) {
            return null;
        }

        return [
            'version' => $version,
            'notes' => isset($release['body']) ? trim((string) $release['body']) : '',
            'asset' => $asset,
            'assets' => $release['assets'],
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $assets
     */
    private function findPharAsset(array $assets): ?array
    {
        foreach ($assets as $asset) {
            if (!isset($asset['name'], $asset['browser_download_url'])) {
                continue;
            }
            if (substr($asset['name'], -5) === '.phar') {
                return [
                    'name' => $asset['name'],
                    'url' => $asset['browser_download_url'],
                    'size' => isset($asset['size']) ? (int) $asset['size'] : 0,
                ];
            }
        }

        return null;
    }

    private function verifyChecksum(array $release, string $filePath, bool $json): bool
    {
        $assets = $release['assets'] ?? [];
        $checksums = [
            'sha256' => $this->findAssetUrl($assets, '.phar.sha256'),
            'sha512' => $this->findAssetUrl($assets, '.phar.sha512'),
            'md5' => $this->findAssetUrl($assets, '.phar.md5'),
        ];

        foreach ($checksums as $algo => $url) {
            if ($url === null) {
                continue;
            }
            $response = $this->httpGet($url);
            if ($response['status'] !== 200) {
                continue;
            }

            $expected = $this->extractHash($response['body']);
            if ($expected === null) {
                continue;
            }

            $actual = hash_file($algo, $filePath);
            if ($actual === $expected) {
                if (!$json) {
                    $this->comment(strtoupper($algo) . ' checksum verified.');
                }
                return true;
            }

            $this->fail("{$algo} checksum mismatch.", $json);
            return false;
        }

        if (!$json) {
            $this->comment('No checksum file found; skipping integrity check.');
        }

        return true;
    }

    /**
     * @param array<int, array<string, mixed>> $assets
     */
    private function findAssetUrl(array $assets, string $suffix): ?string
    {
        foreach ($assets as $asset) {
            if (!isset($asset['name'], $asset['browser_download_url'])) {
                continue;
            }
            if (substr($asset['name'], -strlen($suffix)) === $suffix) {
                return $asset['browser_download_url'];
            }
        }

        return null;
    }

    private function extractHash(string $payload): ?string
    {
        if (preg_match('/([a-f0-9]{32,128})/i', $payload, $matches)) {
            return strtolower($matches[1]);
        }

        return null;
    }

    private function verifyPharRuns(string $pharPath, bool $json): bool
    {
        $phpBinary = PHP_BINARY;
        $process = new Process([$phpBinary, $pharPath, 'version', '--json']);
        $process->setTimeout(30);
        $process->run();

        if (!$process->isSuccessful()) {
            $this->fail('Downloaded Phar failed to execute.', $json);
            return false;
        }

        return true;
    }

    private function downloadFile(string $url, string $target, int $size = 0): bool
    {
        if (!function_exists('curl_init')) {
            $this->error('cURL is required to download updates.');
            return false;
        }

        $handle = fopen($target, 'wb');
        if ($handle === false) {
            $this->error('Unable to write download file.');
            return false;
        }

        $progressBar = null;
        if ($size > 0 && !$this->option('json')) {
            $progressBar = $this->startProgress($size);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $handle);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        $headers = $this->buildRequestHeaders(false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_NOPROGRESS, $progressBar === null);

        if ($progressBar !== null) {
            curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function (
                $resource,
                float $downloadSize,
                float $downloaded,
                float $uploadSize,
                float $uploaded
            ) {
                if ($downloadSize > 0) {
                    $this->setProgress((int) $downloaded);
                }
                return 0;
            });
        }

        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        fclose($handle);

        if ($progressBar !== null) {
            $this->finishProgress();
        }

        if ($result === false || $status < 200 || $status >= 300) {
            $this->error($error ? "Download error: {$error}" : 'Download failed.');
            @unlink($target);
            return false;
        }

        return true;
    }

    /**
     * @return array{status: int, body: string}
     */
    private function httpGet(string $url): array
    {
        $fixture = $this->getFixtureResponse($url);
        if ($fixture !== null) {
            return $fixture;
        }

        if (!function_exists('curl_init')) {
            return [
                'status' => 0,
                'body' => '',
            ];
        }

        $headers = $this->buildRequestHeaders(true);

        $token = getenv('GITHUB_TOKEN');
        if ($token !== false && $token !== '') {
            $headers[] = 'Authorization: token ' . $token;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $body = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [
            'status' => (int) $status,
            'body' => is_string($body) ? $body : '',
        ];
    }

    /**
     * @return array{status: int, body: string}|null
     */
    private function getFixtureResponse(string $url): ?array
    {
        $path = getenv('MODX_CLI_SELF_UPDATE_FIXTURES');
        if ($path === false || $path === '') {
            return null;
        }
        if (!is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        $data = json_decode($raw, true);
        if (!is_array($data) || !isset($data[$url]) || !is_array($data[$url])) {
            return null;
        }

        $entry = $data[$url];
        $status = isset($entry['status']) ? (int) $entry['status'] : 200;
        $body = isset($entry['body']) ? (string) $entry['body'] : '';

        return [
            'status' => $status,
            'body' => $body,
        ];
    }

    /**
     * @return array<int, string>
     */
    private function buildRequestHeaders(bool $acceptJson): array
    {
        $version = $this->getCurrentVersion();
        $userAgent = 'modx-cli';
        if ($version !== null && $version !== '') {
            $userAgent .= '/' . $version;
        }

        $headers = [
            'User-Agent: ' . $userAgent,
        ];

        if ($acceptJson) {
            $headers[] = 'Accept: application/json';
        }

        if ($version !== null && $version !== '') {
            $headers[] = 'X-Modx-CLI-Version: ' . $version;
        }

        return $headers;
    }

    private function normalizeVersion(string $version): string
    {
        return ltrim($version, 'v');
    }

    private function getCurrentVersion(): ?string
    {
        $pharPath = \Phar::running(false);
        if ($pharPath) {
            $path = 'phar://' . $pharPath . '/VERSION';
            if (is_file($path)) {
                return trim((string) file_get_contents($path));
            }
        }

        $rootPath = dirname(__DIR__, 2) . '/VERSION';
        if (is_file($rootPath)) {
            return trim((string) file_get_contents($rootPath));
        }

        $app = $this->getApplication();
        if ($app) {
            return $app->getVersion();
        }

        return null;
    }

    private function isPharInstall(): bool
    {
        return \Phar::running(false) !== '';
    }

    private function getExecutablePath(): string
    {
        $path = $_SERVER['argv'][0] ?? '';
        $resolved = $path ? realpath($path) : false;

        return $resolved ?: $path;
    }

    private function renderReleaseNotes(string $notes): void
    {
        if ($notes === '') {
            return;
        }

        $this->line('');
        $this->comment('Release notes:');
        $this->line($notes);
    }

    private function buildBackupPath(string $pharPath, string $version): string
    {
        $safeVersion = str_replace(['/', '\\', ' '], '-', $version);
        $candidate = $pharPath . '.' . $safeVersion . '.backup';
        if (!file_exists($candidate)) {
            return $candidate;
        }

        return $pharPath . '.' . $safeVersion . '.' . uniqid('', true) . '.backup';
    }

    private function restoreBackup(bool $backupCreated, string $backupPath, string $pharPath): void
    {
        if ($backupCreated && file_exists($backupPath)) {
            @copy($backupPath, $pharPath);
        }
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $index = (int) floor(log($bytes, 1024));
        $index = min($index, count($units) - 1);

        return round($bytes / pow(1024, $index), 2) . ' ' . $units[$index];
    }

    private function fail(string $message, bool $json): int
    {
        if ($json) {
            $payload = [
                'success' => false,
                'message' => $message,
            ];
            $currentVersion = $this->getCurrentVersion();
            if ($currentVersion !== null && $currentVersion !== '') {
                $payload['current_version'] = $currentVersion;
            }
            $this->output->writeln(json_encode($payload, JSON_PRETTY_PRINT));
        } else {
            $this->error($message);
        }

        return 1;
    }
}
