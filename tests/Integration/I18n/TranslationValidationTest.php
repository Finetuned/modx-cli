<?php

declare(strict_types=1);

namespace MODX\CLI\Tests\Integration\I18n;

use MODX\CLI\Translation\TranslationManager;
use MODX\CLI\Translation\TranslationReader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * Integration tests for translation file completeness and consistency.
 *
 * Validates that all non-English YAML files:
 *   - Parse without errors (valid YAML)
 *   - Contain no missing keys relative to the 'en' baseline
 *   - Use consistent %param% placeholder names for each translated string
 *
 * These tests act as a CI guard: any translator contribution that drops a key
 * or renames a placeholder will be caught here before it ships.
 */
class TranslationValidationTest extends TestCase
{
    private TranslationReader $reader;

    protected function setUp(): void
    {
        TranslationManager::reset();
        $this->reader = TranslationReader::create();
    }

    protected function tearDown(): void
    {
        TranslationManager::reset();
    }

    public function testAllExpectedLocalesArePresent(): void
    {
        $locales = $this->reader->getLocales();

        foreach (['de', 'en', 'es', 'fr', 'ru'] as $expected) {
            $this->assertContains($expected, $locales, "Expected locale '{$expected}' to be present in translations/");
        }
    }

    public function testAllDomainsExistForEachLocale(): void
    {
        $requiredDomains = ['errors', 'commands'];

        foreach ($this->reader->getLocales() as $locale) {
            foreach ($requiredDomains as $domain) {
                $path = $this->reader->getFilePath($locale, $domain);
                $this->assertFileExists(
                    $path,
                    "Required file missing: translations/{$locale}/{$domain}.yaml"
                );
            }
        }
    }

    public function testAllTranslationFilesAreValidYaml(): void
    {
        foreach ($this->reader->getLocales() as $locale) {
            foreach ($this->reader->getDomains() as $domain) {
                $path = $this->reader->getFilePath($locale, $domain);
                if (!file_exists($path)) {
                    continue;
                }

                try {
                    $parsed = Yaml::parseFile($path);
                } catch (\Throwable $e) {
                    $this->fail("YAML parse error in translations/{$locale}/{$domain}.yaml: " . $e->getMessage());
                }

                $this->assertTrue(
                    $parsed === null || is_array($parsed),
                    "translations/{$locale}/{$domain}.yaml must parse to an array or null"
                );
            }
        }
    }

    public function testNoMissingKeysInNonEnglishLocales(): void
    {
        $nonEnglishLocales = array_filter($this->reader->getLocales(), fn($l) => $l !== 'en');

        foreach ($nonEnglishLocales as $locale) {
            foreach ($this->reader->getDomains() as $domain) {
                $enPath = $this->reader->getFilePath('en', $domain);
                if (!file_exists($enPath)) {
                    continue;
                }

                $missing = $this->reader->getMissingKeys($locale, $domain);

                $this->assertEmpty(
                    $missing,
                    sprintf(
                        "Missing %d key(s) in translations/%s/%s.yaml: %s",
                        count($missing),
                        $locale,
                        $domain,
                        implode(', ', array_slice($missing, 0, 10))
                    )
                );
            }
        }
    }

    public function testPlaceholderConsistencyAcrossLocales(): void
    {
        $nonEnglishLocales = array_filter($this->reader->getLocales(), fn($l) => $l !== 'en');

        foreach ($this->reader->getDomains() as $domain) {
            $enValues = $this->reader->getValues('en', $domain);

            foreach ($enValues as $key => $enValue) {
                preg_match_all('/%([^%\s]+)%/', $enValue, $matches);
                $enParams = $matches[1];

                if (empty($enParams)) {
                    continue;
                }

                foreach ($nonEnglishLocales as $locale) {
                    $localeValues = $this->reader->getValues($locale, $domain);

                    if (!isset($localeValues[$key]) || $localeValues[$key] === '') {
                        // Missing key — caught by testNoMissingKeysInNonEnglishLocales
                        continue;
                    }

                    $localValue = $localeValues[$key];
                    preg_match_all('/%([^%\s]+)%/', $localValue, $localeMatches);
                    $localeParams = $localeMatches[1];

                    foreach ($enParams as $param) {
                        $this->assertContains(
                            $param,
                            $localeParams,
                            sprintf(
                                "Placeholder '%%%s%%' present in en/%s.yaml key '%s' is missing from %s/%s.yaml",
                                $param,
                                $domain,
                                $key,
                                $locale,
                                $domain
                            )
                        );
                    }
                }
            }
        }
    }

    public function testEnglishBaselineHasExpectedCommandKeys(): void
    {
        $keys = $this->reader->getKeys('en', 'commands');

        $expectedPrefixes = [
            'resource.create.success',
            'config.add.added',
            'context.create.success',
            'source.create.success',
            'version.cli_version',
            'selfupdate.update_complete',
            'crawl.completed',
            'find.version_unsupported',
        ];

        foreach ($expectedPrefixes as $expected) {
            $this->assertContains(
                $expected,
                $keys,
                "Expected key '{$expected}' in translations/en/commands.yaml"
            );
        }
    }

    public function testEnglishBaselineHasExpectedErrorKeys(): void
    {
        $keys = $this->reader->getKeys('en', 'errors');

        $this->assertContains(
            'operation_aborted',
            $keys,
            "Expected key 'operation_aborted' in translations/en/errors.yaml"
        );
    }
}
