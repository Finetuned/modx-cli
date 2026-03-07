<?php

declare(strict_types=1);

namespace MODX\CLI\Translation;

use Symfony\Component\Yaml\Yaml;

/**
 * Reads and introspects YAML translation files.
 *
 * Provides flat dot-notation key access, missing-key diffing,
 * and nested-array serialization for the i18n:* commands.
 */
class TranslationReader
{
    const DOMAINS = ['messages', 'errors', 'commands', 'validation', 'success'];
    const BASE_LOCALE = 'en';

    private string $translationsPath;

    public function __construct(string $translationsPath)
    {
        $this->translationsPath = rtrim($translationsPath, '/');
    }

    public static function create(): self
    {
        return new self(realpath(__DIR__ . '/../../translations') ?: '');
    }

    /**
     * Returns available locale subdirectory names.
     */
    public function getLocales(): array
    {
        return TranslationManager::getInstance()->getAvailableLocales();
    }

    /**
     * Returns the list of known translation domains.
     */
    public function getDomains(): array
    {
        return self::DOMAINS;
    }

    /**
     * Returns flat dot-notation keys for a locale/domain YAML file.
     * Returns [] if the file does not exist.
     */
    public function getKeys(string $locale, string $domain): array
    {
        return array_keys($this->getValues($locale, $domain));
    }

    /**
     * Returns flat dot-notation key => value pairs for a locale/domain YAML file.
     * Returns [] if the file does not exist or is empty.
     */
    public function getValues(string $locale, string $domain): array
    {
        $file = $this->getFilePath($locale, $domain);
        if (!file_exists($file)) {
            return [];
        }

        $data = Yaml::parseFile($file);
        if (!is_array($data)) {
            return [];
        }

        return $this->flatten($data);
    }

    /**
     * Returns keys present in base locale but absent in target locale for a domain.
     */
    public function getMissingKeys(string $targetLocale, string $domain): array
    {
        $baseKeys = $this->getKeys(self::BASE_LOCALE, $domain);
        $targetKeys = $this->getKeys($targetLocale, $domain);

        return array_values(array_diff($baseKeys, $targetKeys));
    }

    /**
     * Converts flat dot-notation array to nested associative array.
     * Used when writing new keys back to YAML files.
     *
     * Example: ['resource.create.success' => ''] → ['resource' => ['create' => ['success' => '']]]
     */
    public function unflatten(array $flat): array
    {
        $result = [];
        foreach ($flat as $dotKey => $value) {
            $this->setNested($result, explode('.', (string) $dotKey), $value);
        }
        return $result;
    }

    /**
     * Returns the absolute path to a translation file.
     */
    public function getFilePath(string $locale, string $domain): string
    {
        return $this->translationsPath . '/' . $locale . '/' . $domain . '.yaml';
    }

    /**
     * Recursively flattens a nested array to dot-notation keys.
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $fullKey = $prefix !== '' ? $prefix . '.' . $key : (string) $key;
            if (is_array($value)) {
                $result = array_merge($result, $this->flatten($value, $fullKey));
            } else {
                $result[$fullKey] = (string) ($value ?? '');
            }
        }
        return $result;
    }

    /**
     * Sets a value in a nested array using a key path.
     */
    private function setNested(array &$target, array $keys, string $value): void
    {
        $key = array_shift($keys);
        if ($keys === []) {
            $target[$key] = $value;
            return;
        }
        if (!isset($target[$key]) || !is_array($target[$key])) {
            $target[$key] = [];
        }
        $this->setNested($target[$key], $keys, $value);
    }
}
