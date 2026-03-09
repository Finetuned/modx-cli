<?php

declare(strict_types=1);

namespace MODX\CLI\Tests\Integration\I18n;

use MODX\CLI\Translation\TranslationManager;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for locale switching behaviour.
 *
 * Validates that TranslationManager correctly detects and switches locales at
 * runtime, and that translated strings differ from English where expected.
 *
 * These tests exercise the actual YAML translation files on disk.
 */
class LocaleSwitchingTest extends TestCase
{
    protected function setUp(): void
    {
        TranslationManager::reset();
    }

    protected function tearDown(): void
    {
        TranslationManager::reset();
    }

    public function testDefaultLocaleIsEn(): void
    {
        $manager = TranslationManager::getInstance();
        $this->assertEquals('en', $manager->getLocale());
    }

    public function testDetectLocaleFromCliOption(): void
    {
        $manager = TranslationManager::getInstance();
        $locale = $manager->detectLocale(['locale' => 'fr']);
        $this->assertEquals('fr', $locale);
    }

    public function testDetectLocaleNormalizesRegionCode(): void
    {
        $manager = TranslationManager::getInstance();
        $locale = $manager->detectLocale(['locale' => 'fr_FR']);
        $this->assertEquals('fr', $locale);
    }

    public function testDetectLocaleNormalizesUtf8Encoding(): void
    {
        $manager = TranslationManager::getInstance();
        $locale = $manager->detectLocale(['locale' => 'de_DE.UTF-8']);
        $this->assertEquals('de', $locale);
    }

    public function testDetectLocaleFromEnvVar(): void
    {
        $original = getenv('MODX_CLI_LOCALE');

        try {
            putenv('MODX_CLI_LOCALE=de');
            $manager = TranslationManager::getInstance();
            $locale = $manager->detectLocale([]);
            $this->assertEquals('de', $locale);
        } finally {
            if ($original === false) {
                putenv('MODX_CLI_LOCALE');
            } else {
                putenv('MODX_CLI_LOCALE=' . $original);
            }
        }
    }

    public function testEnvVarTakesPrecedenceOverSystemLang(): void
    {
        $originalEnv = getenv('MODX_CLI_LOCALE');
        $originalLang = getenv('LANG');

        try {
            putenv('MODX_CLI_LOCALE=fr');
            putenv('LANG=de_DE.UTF-8');

            $manager = TranslationManager::getInstance();
            $locale = $manager->detectLocale([]);
            $this->assertEquals('fr', $locale);
        } finally {
            if ($originalEnv === false) {
                putenv('MODX_CLI_LOCALE');
            } else {
                putenv('MODX_CLI_LOCALE=' . $originalEnv);
            }
            if ($originalLang === false) {
                putenv('LANG');
            } else {
                putenv('LANG=' . $originalLang);
            }
        }
    }

    public function testCliOptionTakesPrecedenceOverEnvVar(): void
    {
        $original = getenv('MODX_CLI_LOCALE');

        try {
            putenv('MODX_CLI_LOCALE=de');
            $manager = TranslationManager::getInstance();
            $locale = $manager->detectLocale(['locale' => 'fr']);
            $this->assertEquals('fr', $locale);
        } finally {
            if ($original === false) {
                putenv('MODX_CLI_LOCALE');
            } else {
                putenv('MODX_CLI_LOCALE=' . $original);
            }
        }
    }

    public function testSetLocaleChangesTranslation(): void
    {
        $manager = TranslationManager::getInstance();
        $manager->setLocale('fr');

        $result = $manager->getTranslator()->trans('resource.create.success', [], 'commands');
        $this->assertEquals('Ressource créée avec succès', $result);
    }

    public function testFrenchTranslationDiffersFromEnglish(): void
    {
        $manager = TranslationManager::getInstance();

        $manager->setLocale('en');
        $enResult = $manager->getTranslator()->trans('resource.create.success', [], 'commands');

        // Reset and switch to French
        TranslationManager::reset();
        $manager = TranslationManager::getInstance();
        $manager->setLocale('fr');
        $frResult = $manager->getTranslator()->trans('resource.create.success', [], 'commands');

        $this->assertNotEquals($enResult, $frResult);
        $this->assertEquals('Resource created successfully', $enResult);
        $this->assertEquals('Ressource créée avec succès', $frResult);
    }

    public function testGermanTranslationDiffersFromEnglish(): void
    {
        $manager = TranslationManager::getInstance();

        $manager->setLocale('en');
        $enResult = $manager->getTranslator()->trans('resource.create.success', [], 'commands');

        TranslationManager::reset();
        $manager = TranslationManager::getInstance();
        $manager->setLocale('de');
        $deResult = $manager->getTranslator()->trans('resource.create.success', [], 'commands');

        $this->assertNotEquals($enResult, $deResult);
    }

    public function testSpanishTranslationDiffersFromEnglish(): void
    {
        $manager = TranslationManager::getInstance();

        $manager->setLocale('en');
        $enResult = $manager->getTranslator()->trans('resource.create.success', [], 'commands');

        TranslationManager::reset();
        $manager = TranslationManager::getInstance();
        $manager->setLocale('es');
        $esResult = $manager->getTranslator()->trans('resource.create.success', [], 'commands');

        $this->assertNotEquals($enResult, $esResult);
    }

    public function testRussianTranslationDiffersFromEnglish(): void
    {
        $manager = TranslationManager::getInstance();

        $manager->setLocale('en');
        $enResult = $manager->getTranslator()->trans('resource.create.success', [], 'commands');

        TranslationManager::reset();
        $manager = TranslationManager::getInstance();
        $manager->setLocale('ru');
        $ruResult = $manager->getTranslator()->trans('resource.create.success', [], 'commands');

        $this->assertNotEquals($enResult, $ruResult);
    }

    public function testFallbackToEnglishForUnavailableLocale(): void
    {
        $manager = TranslationManager::getInstance();
        $manager->setLocale('zh');

        // Symfony falls back to 'en' for missing locale — key is returned as-is if
        // not found anywhere, but since 'en' has it, fallback should deliver English.
        $result = $manager->getTranslator()->trans('resource.create.success', [], 'commands');
        $this->assertEquals('Resource created successfully', $result);
    }

    public function testParameterSubstitutionInEnglish(): void
    {
        $manager = TranslationManager::getInstance();
        $manager->setLocale('en');

        $result = $manager->getTranslator()->trans(
            'resource.erase.not_in_trash',
            ['%pagetitle%' => 'Home', '%id%' => '42'],
            'commands'
        );

        $this->assertStringContainsString('Home', $result);
        $this->assertStringContainsString('42', $result);
    }

    public function testParameterSubstitutionInFrench(): void
    {
        $manager = TranslationManager::getInstance();
        $manager->setLocale('fr');

        $result = $manager->getTranslator()->trans(
            'resource.erase.not_in_trash',
            ['%pagetitle%' => 'Accueil', '%id%' => '7'],
            'commands'
        );

        $this->assertStringContainsString('Accueil', $result);
        $this->assertStringContainsString('7', $result);
    }

    public function testErrorsDomainTranslatesInFrench(): void
    {
        $manager = TranslationManager::getInstance();
        $manager->setLocale('fr');

        $enResult = $manager->getTranslator()->trans('operation_aborted', [], 'errors');

        TranslationManager::reset();
        $manager = TranslationManager::getInstance();
        $manager->setLocale('en');
        $frResult = $manager->getTranslator()->trans('operation_aborted', [], 'errors');

        // Both locales should return a non-empty string for this key
        $this->assertNotEmpty($enResult);
        $this->assertNotEmpty($frResult);
    }

    public function testSetLocaleNormalizesRegionCodeOnInstance(): void
    {
        $manager = TranslationManager::getInstance();
        $manager->setLocale('fr_FR');
        $this->assertEquals('fr', $manager->getLocale());
    }

    public function testAllExpectedLocalesAreAvailable(): void
    {
        $manager = TranslationManager::getInstance();
        $locales = $manager->getAvailableLocales();

        foreach (['de', 'en', 'es', 'fr', 'ru'] as $expected) {
            $this->assertContains($expected, $locales, "Expected locale '{$expected}' to be available");
        }
    }
}
