<?php

declare(strict_types=1);

namespace MODX\CLI\Tests\Translation;

use MODX\CLI\Translation\TranslationManager;
use MODX\CLI\Translation\TranslationReader;
use PHPUnit\Framework\TestCase;

class TranslationReaderTest extends TestCase
{
    private TranslationReader $reader;

    /**
     * @var string[]
     */
    private array $temporaryPaths = [];

    protected function setUp(): void
    {
        $this->reader = TranslationReader::create();
    }

    protected function tearDown(): void
    {
        TranslationManager::reset();

        foreach ($this->temporaryPaths as $path) {
            $this->removePath($path);
        }
    }

    public function testCreateReturnsInstance(): void
    {
        $reader = TranslationReader::create();

        $this->assertInstanceOf(TranslationReader::class, $reader);
    }

    public function testGetDomainsReturnsFiveDomains(): void
    {
        $domains = $this->reader->getDomains();

        $this->assertCount(5, $domains);
        $this->assertContains('messages', $domains);
        $this->assertContains('errors', $domains);
        $this->assertContains('commands', $domains);
        $this->assertContains('validation', $domains);
        $this->assertContains('success', $domains);
    }

    public function testGetLocalesIncludesBaseLocale(): void
    {
        $locales = $this->reader->getLocales();

        $this->assertContains('en', $locales);
    }

    public function testGetKeysReturnsFlatDotNotationKeys(): void
    {
        $keys = $this->reader->getKeys('en', 'errors');

        $this->assertIsArray($keys);
        $this->assertNotEmpty($keys);

        // All keys must be strings
        foreach ($keys as $key) {
            $this->assertIsString($key);
        }
    }

    public function testGetValuesReturnsDotNotationMap(): void
    {
        $values = $this->reader->getValues('en', 'errors');

        $this->assertIsArray($values);
        $this->assertNotEmpty($values);

        foreach ($values as $key => $value) {
            $this->assertIsString($key);
            $this->assertIsString($value);
        }
    }

    public function testGetValuesReturnsEmptyForNonExistentLocale(): void
    {
        $values = $this->reader->getValues('xx', 'errors');

        $this->assertSame([], $values);
    }

    public function testGetValuesReturnsEmptyForEmptyYamlFile(): void
    {
        $path = $this->createTempTranslationsPath();
        $this->writeTranslationFile($path, 'en', 'messages', '');
        $reader = new TranslationReader($path);

        $this->assertSame([], $reader->getValues('en', 'messages'));
    }

    public function testGetValuesFlattensControlledNestedFixture(): void
    {
        $path = $this->createTempTranslationsPath();
        $this->writeTranslationFile(
            $path,
            'en',
            'messages',
            "alpha:\n  beta:\n    gamma: value\nempty:\n"
        );
        $reader = new TranslationReader($path);

        $this->assertSame(
            [
                'alpha.beta.gamma' => 'value',
                'empty' => '',
            ],
            $reader->getValues('en', 'messages')
        );
    }

    public function testGetMissingKeysReturnsEmptyForBaseLocale(): void
    {
        // en vs en — should have no missing keys
        $missing = $this->reader->getMissingKeys('en', 'errors');

        $this->assertSame([], $missing);
    }

    public function testGetMissingKeysDetectsAbsentKeys(): void
    {
        // 'xx' locale has no files, so all base keys are missing
        $missing = $this->reader->getMissingKeys('xx', 'errors');
        $baseKeys = $this->reader->getKeys('en', 'errors');

        $this->assertSame($baseKeys, $missing);
    }

    public function testUnflattenConvertsDotNotationToNestedArray(): void
    {
        $flat = [
            'resource.create.success' => 'ok',
            'resource.create.failed'  => 'fail',
        ];

        $nested = $this->reader->unflatten($flat);

        $this->assertArrayHasKey('resource', $nested);
        $this->assertArrayHasKey('create', $nested['resource']);
        $this->assertSame('ok', $nested['resource']['create']['success']);
        $this->assertSame('fail', $nested['resource']['create']['failed']);
    }

    public function testUnflattenHandlesSingleLevelKeys(): void
    {
        $flat = ['foo' => 'bar'];
        $nested = $this->reader->unflatten($flat);

        $this->assertSame(['foo' => 'bar'], $nested);
    }

    private function createTempTranslationsPath(): string
    {
        $path = sys_get_temp_dir() . '/modx-cli-translation-reader-test-' . bin2hex(random_bytes(6));
        mkdir($path, 0777, true);
        $this->temporaryPaths[] = $path;

        return $path;
    }

    private function writeTranslationFile(string $basePath, string $locale, string $domain, string $contents): void
    {
        $localePath = $basePath . '/' . $locale;
        if (!is_dir($localePath)) {
            mkdir($localePath, 0777, true);
        }

        file_put_contents($localePath . '/' . $domain . '.yaml', $contents);
    }

    private function removePath(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (is_file($path) || is_link($path)) {
            unlink($path);
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            $item->isDir() ? rmdir((string) $item) : unlink((string) $item);
        }

        rmdir($path);
    }
}
