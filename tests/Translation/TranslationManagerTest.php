<?php

declare(strict_types=1);

namespace MODX\CLI\Tests\Translation;

use MODX\CLI\Translation\TranslationManager;
use PHPUnit\Framework\TestCase;

class TranslationManagerTest extends TestCase
{
    protected function tearDown(): void
    {
        TranslationManager::reset();
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

        $prev = getenv('MODX_CLI_LOCALE');
        putenv('MODX_CLI_LOCALE=de');

        try {
            $locale = $manager->detectLocale();
            $this->assertSame('de', $locale);
        } finally {
            if ($prev === false) {
                putenv('MODX_CLI_LOCALE');
            } else {
                putenv('MODX_CLI_LOCALE=' . $prev);
            }
        }
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

    public function testGetAvailableLocalesIncludesEn(): void
    {
        $manager = TranslationManager::getInstance();
        $locales = $manager->getAvailableLocales();

        $this->assertContains('en', $locales);
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
}
