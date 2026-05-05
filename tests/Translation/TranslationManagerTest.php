<?php

declare(strict_types=1);

namespace MODX\CLI\Tests\Translation;

use MODX\CLI\Translation\TranslationManager;
use PHPUnit\Framework\TestCase;

class TranslationManagerTest extends TestCase
{
    private const ENV_VARS = ['MODX_CLI_LOCALE', 'LANG', 'LC_ALL'];

    /**
     * @var array<string, string|false>
     */
    private array $originalEnv = [];

    /**
     * @var string[]
     */
    private array $temporaryPaths = [];

    private ?string $originalLocaleDefault = null;

    protected function setUp(): void
    {
        TranslationManager::reset();

        foreach (self::ENV_VARS as $name) {
            $this->originalEnv[$name] = getenv($name);
        }

        if (class_exists('\Locale')) {
            $this->originalLocaleDefault = \Locale::getDefault();
        }
    }

    protected function tearDown(): void
    {
        TranslationManager::reset();

        foreach ($this->temporaryPaths as $path) {
            $this->removePath($path);
        }

        foreach ($this->originalEnv as $name => $value) {
            $this->restoreEnv($name, $value);
        }

        if ($this->originalLocaleDefault !== null && class_exists('\Locale')) {
            \Locale::setDefault($this->originalLocaleDefault);
        }
    }

    public function testGetInstanceReturnsSameInstance(): void
    {
        $a = TranslationManager::getInstance();
        $b = TranslationManager::getInstance();

        $this->assertSame($a, $b);
    }

    public function testResetClearsSingleton(): void
    {
        $a = TranslationManager::getInstance();
        TranslationManager::reset();
        $b = TranslationManager::getInstance();

        $this->assertNotSame($a, $b);
    }

    public function testDetectLocaleFromCliOption(): void
    {
        $manager = TranslationManager::getInstance();
        $locale = $manager->detectLocale(['locale' => 'fr']);

        $this->assertSame('fr', $locale);
    }

    public function testDetectLocaleFromCliOptionNormalizesRegionCode(): void
    {
        $manager = TranslationManager::getInstance();
        $locale = $manager->detectLocale(['locale' => 'fr_FR']);

        $this->assertSame('fr', $locale);
    }

    public function testDetectLocaleFromEnvVar(): void
    {
        $manager = TranslationManager::getInstance();

        $this->setEnv('MODX_CLI_LOCALE', 'de');

        $locale = $manager->detectLocale();
        $this->assertSame('de', $locale);
    }

    public function testDetectLocaleFromLangEnvVar(): void
    {
        $this->setEnv('MODX_CLI_LOCALE', null);
        $this->setEnv('LANG', 'es_ES.UTF-8');
        $this->setEnv('LC_ALL', null);

        $manager = TranslationManager::getInstance();

        $this->assertSame('es', $manager->detectLocale());
    }

    public function testDetectLocaleFromLcAllEnvVar(): void
    {
        $this->setEnv('MODX_CLI_LOCALE', null);
        $this->setEnv('LANG', null);
        $this->setEnv('LC_ALL', 'ru_RU.UTF-8');

        $manager = TranslationManager::getInstance();

        $this->assertSame('ru', $manager->detectLocale());
    }

    public function testDetectLocaleFromSystemLocale(): void
    {
        if (!class_exists('\Locale')) {
            $this->markTestSkipped('The intl extension is required for system locale detection.');
        }

        $this->setEnv('MODX_CLI_LOCALE', null);
        $this->setEnv('LANG', null);
        $this->setEnv('LC_ALL', null);
        \Locale::setDefault('de_DE');

        $manager = TranslationManager::getInstance();

        $this->assertSame('de', $manager->detectLocale());
    }

    public function testDetectLocaleFallsBackToEnglishForInvalidLocale(): void
    {
        $manager = TranslationManager::getInstance();

        $this->assertSame('en', $manager->detectLocale(['locale' => 'invalid']));
    }

    public function testSetAndGetLocale(): void
    {
        $manager = TranslationManager::getInstance();
        $manager->setLocale('ru');

        $this->assertSame('ru', $manager->getLocale());
    }

    public function testSetLocaleNormalizesRegionCode(): void
    {
        $manager = TranslationManager::getInstance();
        $manager->setLocale('es-MX');

        $this->assertSame('es', $manager->getLocale());
    }

    public function testSetLocaleNormalizesUppercaseLanguageCode(): void
    {
        $manager = TranslationManager::getInstance();
        $manager->setLocale('RU');

        $this->assertSame('ru', $manager->getLocale());
    }

    public function testSetLocaleUpdatesInitializedTranslator(): void
    {
        $manager = TranslationManager::getInstance();
        $translator = $manager->getTranslator();

        $manager->setLocale('fr');

        $this->assertSame('fr', $translator->getLocale());
    }

    public function testGetAvailableLocalesIncludesEn(): void
    {
        $manager = TranslationManager::getInstance();
        $locales = $manager->getAvailableLocales();

        $this->assertContains('en', $locales);
    }

    public function testGetAvailableLocalesFiltersInvalidTranslationEntries(): void
    {
        $path = $this->createTempTranslationsPath();
        mkdir($path . '/zz');
        mkdir($path . '/eng');
        file_put_contents($path . '/README', 'not a locale');

        $manager = TranslationManager::getInstance();
        $manager->addTranslationPath($path);
        $manager->addTranslationPath($path . '-missing');

        $locales = $manager->getAvailableLocales();

        $this->assertContains('zz', $locales);
        $this->assertNotContains('eng', $locales);
        $this->assertNotContains('README', $locales);
    }

    public function testTranslatorLoadsAvailableFilesAndSkipsMissingPaths(): void
    {
        $primaryPath = $this->createTempTranslationsPath();
        $secondaryPath = $this->createTempTranslationsPath();
        $this->writeTranslationFile($primaryPath, 'zz', 'messages', "fixture:\n  loaded: Custom message\n");

        $manager = TranslationManager::getInstance();
        $manager->addTranslationPath($primaryPath);
        $manager->addTranslationPath($secondaryPath);
        $manager->addTranslationPath($primaryPath . '-missing');

        $translator = $manager->getTranslator();
        $manager->setLocale('zz');

        $this->assertSame('Custom message', $translator->trans('fixture.loaded', [], 'messages'));
    }

    public function testIsLocaleAvailableReturnsTrueForEn(): void
    {
        $manager = TranslationManager::getInstance();

        $this->assertTrue($manager->isLocaleAvailable('en'));
    }

    public function testIsLocaleAvailableReturnsFalseForUnknown(): void
    {
        $manager = TranslationManager::getInstance();

        $this->assertFalse($manager->isLocaleAvailable('xx'));
    }

    public function testGetFallbackLocaleIsAlwaysEn(): void
    {
        $manager = TranslationManager::getInstance();

        $this->assertSame('en', $manager->getFallbackLocale());
    }

    private function setEnv(string $name, ?string $value): void
    {
        if ($value === null) {
            putenv($name);
            return;
        }

        putenv($name . '=' . $value);
    }

    /**
     * @param string|false $value
     */
    private function restoreEnv(string $name, $value): void
    {
        if ($value === false) {
            putenv($name);
            return;
        }

        putenv($name . '=' . $value);
    }

    private function createTempTranslationsPath(): string
    {
        $path = sys_get_temp_dir() . '/modx-cli-translation-test-' . bin2hex(random_bytes(6));
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
