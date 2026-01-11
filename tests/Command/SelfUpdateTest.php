<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\SelfUpdate;
use PHPUnit\Framework\TestCase;

class SelfUpdateTest extends TestCase
{
    public function testNormalizeVersionStripsLeadingV()
    {
        $command = new SelfUpdate();
        $normalized = $this->callPrivate($command, 'normalizeVersion', ['v0.6.0']);
        $this->assertSame('0.6.0', $normalized);
    }

    public function testExtractHashParsesHashFromPayload()
    {
        $command = new SelfUpdate();
        $payload = "checksum: AABBCCDDEEFF00112233445566778899AABBCCDDEEFF00112233445566778899\n";
        $hash = $this->callPrivate($command, 'extractHash', [$payload]);
        $this->assertSame('aabbccddeeff00112233445566778899aabbccddeeff00112233445566778899', $hash);
    }

    public function testExtractHashParsesHashWithFilename()
    {
        $command = new SelfUpdate();
        $payload = "aabbccddeeff00112233445566778899  modx-cli.phar\n";
        $hash = $this->callPrivate($command, 'extractHash', [$payload]);
        $this->assertSame('aabbccddeeff00112233445566778899', $hash);
    }

    public function testSelectLatestReleaseStableSkipsPrerelease()
    {
        $command = new SelfUpdate();
        $releases = [
            [
                'tag_name' => 'v0.6.0',
                'prerelease' => false,
                'assets' => [
                    [
                        'name' => 'modx-cli.phar',
                        'browser_download_url' => 'https://example.com/modx-cli.phar',
                        'size' => 123,
                    ],
                ],
            ],
            [
                'tag_name' => 'v0.7.0-alpha',
                'prerelease' => true,
                'assets' => [
                    [
                        'name' => 'modx-cli.phar',
                        'browser_download_url' => 'https://example.com/modx-cli-alpha.phar',
                        'size' => 456,
                    ],
                ],
            ],
            [
                'tag_name' => 'v0.6.1',
                'prerelease' => false,
                'assets' => [
                    [
                        'name' => 'modx-cli.phar',
                        'browser_download_url' => 'https://example.com/modx-cli-061.phar',
                        'size' => 789,
                    ],
                ],
            ],
        ];

        $latest = $this->callPrivate($command, 'selectLatestRelease', [$releases, false]);
        $this->assertIsArray($latest);
        $this->assertSame('0.6.1', $latest['version']);
    }

    public function testSelectLatestReleaseNightlyIncludesPrerelease()
    {
        $command = new SelfUpdate();
        $releases = [
            [
                'tag_name' => 'v0.6.2',
                'prerelease' => false,
                'assets' => [
                    [
                        'name' => 'modx-cli.phar',
                        'browser_download_url' => 'https://example.com/modx-cli.phar',
                        'size' => 123,
                    ],
                ],
            ],
            [
                'tag_name' => 'v0.7.0-alpha',
                'prerelease' => true,
                'assets' => [
                    [
                        'name' => 'modx-cli.phar',
                        'browser_download_url' => 'https://example.com/modx-cli-alpha.phar',
                        'size' => 456,
                    ],
                ],
            ],
        ];

        $latest = $this->callPrivate($command, 'selectLatestRelease', [$releases, true]);
        $this->assertIsArray($latest);
        $this->assertSame('0.7.0-alpha', $latest['version']);
    }

    public function testNormalizeReleaseRequiresPharAsset()
    {
        $command = new SelfUpdate();
        $release = [
            'tag_name' => 'v0.6.0',
            'assets' => [
                [
                    'name' => 'notes.txt',
                    'browser_download_url' => 'https://example.com/notes.txt',
                    'size' => 10,
                ],
            ],
        ];

        $normalized = $this->callPrivate($command, 'normalizeRelease', [$release]);
        $this->assertNull($normalized);
    }

    public function testNormalizeReleaseMissingTagNameReturnsNull()
    {
        $command = new SelfUpdate();
        $release = [
            'assets' => [
                [
                    'name' => 'modx-cli.phar',
                    'browser_download_url' => 'https://example.com/modx-cli.phar',
                    'size' => 123,
                ],
            ],
        ];

        $normalized = $this->callPrivate($command, 'normalizeRelease', [$release]);
        $this->assertNull($normalized);
    }

    public function testNormalizeReleaseEmptyAssetsReturnsNull()
    {
        $command = new SelfUpdate();
        $release = [
            'tag_name' => 'v0.6.0',
            'assets' => [],
        ];

        $normalized = $this->callPrivate($command, 'normalizeRelease', [$release]);
        $this->assertNull($normalized);
    }

    public function testBuildRequestHeadersIncludeVersionAndJson()
    {
        $command = new SelfUpdate();
        $headers = $this->callPrivate($command, 'buildRequestHeaders', [true]);

        $version = trim((string) file_get_contents(dirname(__DIR__, 2) . '/VERSION'));
        $this->assertNotSame('', $version);

        $this->assertContains('Accept: application/json', $headers);
        $this->assertContains('User-Agent: modx-cli/' . $version, $headers);
        $this->assertContains('X-Modx-CLI-Version: ' . $version, $headers);

        $headersNoJson = $this->callPrivate($command, 'buildRequestHeaders', [false]);
        $this->assertNotContains('Accept: application/json', $headersNoJson);
    }

    public function testBuildRequestHeadersOmitVersionWhenVersionFileEmpty()
    {
        $versionPath = dirname(__DIR__, 2) . '/VERSION';
        $original = is_file($versionPath) ? (string) file_get_contents($versionPath) : null;

        file_put_contents($versionPath, '');
        try {
            $command = new SelfUpdate();
            $headers = $this->callPrivate($command, 'buildRequestHeaders', [false]);
            $this->assertContains('User-Agent: modx-cli', $headers);
            $this->assertNotContains('X-Modx-CLI-Version: ', $headers);
            $this->assertNotContains('User-Agent: modx-cli/', $headers);
        } finally {
            if ($original !== null) {
                file_put_contents($versionPath, $original);
            }
        }
    }

    public function testConflictingTargetOptionsReturnJsonError()
    {
        $command = new SelfUpdate();
        $tester = new \Symfony\Component\Console\Tester\CommandTester($command);

        $tester->execute([
            '--target-version' => '0.1.0',
            '--to' => '0.2.0',
            '--check' => true,
            '--json' => true,
        ]);

        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertFalse($decoded['success']);
        $this->assertSame('Use only one of --target-version or --to.', $decoded['message']);
    }

    /**
     * @param array<int, mixed> $args
     * @return mixed
     */
    private function callPrivate(object $object, string $method, array $args = [])
    {
        $reflection = new \ReflectionMethod($object, $method);
        $reflection->setAccessible(true);

        return $reflection->invokeArgs($object, $args);
    }
}
