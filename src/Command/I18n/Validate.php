<?php

namespace MODX\CLI\Command\I18n;

use MODX\CLI\Command\BaseCmd;
use MODX\CLI\Translation\TranslationReader;

/**
 * Validate translation files for completeness and consistency.
 *
 * Exit code 1 if any issues are found (CI-friendly).
 */
class Validate extends BaseCmd
{
    public const MODX = false;

    protected $name = 'i18n:validate';
    protected $description = 'Validate translation files for completeness and consistency';

    /**
     * Get command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [];
    }

    /**
     * Validate translations and render the selected output format.
     *
     * @return integer
     */
    protected function process()
    {
        $reader = TranslationReader::create();
        $locales = $this->resolveLocales($reader);
        $domains = $reader->getDomains();

        $results = $this->validate($reader, $locales, $domains);

        if ($this->option('json')) {
            $this->output->writeln(json_encode($results, JSON_PRETTY_PRINT));
            return $this->hasIssues($results) ? 1 : 0;
        }

        $this->renderResults($results, $reader);
        return $this->hasIssues($results) ? 1 : 0;
    }

    /**
     * Resolve locales to validate from the command option.
     *
     * @param TranslationReader $reader Translation reader.
     *
     * @return array
     */
    private function resolveLocales(TranslationReader $reader): array
    {
        $filter = $this->option('locale');
        if ($filter !== null) {
            return [$filter];
        }
        return $reader->getLocales();
    }

    /**
     * Validate all selected locales.
     *
     * @param TranslationReader $reader  Translation reader.
     * @param array             $locales Locale names.
     * @param array             $domains Domain names.
     *
     * @return array
     */
    private function validate(TranslationReader $reader, array $locales, array $domains): array
    {
        $results = [];
        foreach ($locales as $locale) {
            $results[$locale] = $this->validateLocale($reader, $locale, $domains);
        }
        return $results;
    }

    /**
     * Validate one locale across all selected domains.
     *
     * @param TranslationReader $reader  Translation reader.
     * @param string            $locale  Locale name.
     * @param array             $domains Domain names.
     *
     * @return array
     */
    private function validateLocale(TranslationReader $reader, string $locale, array $domains): array
    {
        $issues = [];
        foreach ($domains as $domain) {
            $domainIssues = $this->validateDomain($reader, $locale, $domain);
            if ($domainIssues !== []) {
                $issues[$domain] = $domainIssues;
            }
        }
        return $issues;
    }

    /**
     * Validate one locale/domain pair.
     *
     * @param TranslationReader $reader Translation reader.
     * @param string            $locale Locale name.
     * @param string            $domain Domain name.
     *
     * @return array
     */
    private function validateDomain(TranslationReader $reader, string $locale, string $domain): array
    {
        $issues = [];
        $missing = $reader->getMissingKeys($locale, $domain);
        if ($missing !== []) {
            $issues['missing'] = $missing;
        }

        if ($locale === TranslationReader::BASE_LOCALE) {
            $empty = $this->findEmptyValues($reader, $locale, $domain);
            if ($empty !== []) {
                $issues['empty'] = $empty;
            }
        }

        return $issues;
    }

    /**
     * Find empty values in a locale/domain file.
     *
     * @param TranslationReader $reader Translation reader.
     * @param string            $locale Locale name.
     * @param string            $domain Domain name.
     *
     * @return array
     */
    private function findEmptyValues(TranslationReader $reader, string $locale, string $domain): array
    {
        $values = $reader->getValues($locale, $domain);
        $empty = [];
        foreach ($values as $key => $value) {
            if ($value === '') {
                $empty[] = $key;
            }
        }
        return $empty;
    }

    /**
     * Check whether validation results contain issues.
     *
     * @param array $results Validation results.
     *
     * @return boolean
     */
    private function hasIssues(array $results): bool
    {
        foreach ($results as $localeIssues) {
            if ($localeIssues !== []) {
                return true;
            }
        }
        return false;
    }

    /**
     * Render validation results.
     *
     * @param array             $results Validation results.
     * @param TranslationReader $reader  Translation reader.
     *
     * @return void
     */
    private function renderResults(array $results, TranslationReader $reader): void
    {
        $this->output->writeln('<info>Validating translations...</info>');
        $this->output->writeln('');

        foreach ($results as $locale => $issues) {
            $this->renderLocaleResult($locale, $issues, $reader);
        }

        if ($this->hasIssues($results)) {
            $this->output->writeln('');
            $this->output->writeln('<comment>Run `modx i18n:missing <locale>` to see missing keys.</comment>');
        }
    }

    /**
     * Render validation results for one locale.
     *
     * @param string            $locale Locale name.
     * @param array             $issues Locale issues.
     * @param TranslationReader $reader Translation reader.
     *
     * @return void
     */
    private function renderLocaleResult(string $locale, array $issues, TranslationReader $reader): void
    {
        $totalBase = $this->countBaseKeys($reader);

        if ($issues === []) {
            $this->output->writeln(sprintf('<info>[OK]</info>  %s — %d keys, all complete', $locale, $totalBase));
            return;
        }

        $missingCount = $this->countMissing($issues);
        $emptyCount   = $this->countEmpty($issues);
        $label = sprintf('<error>[FAIL]</error> %s', $locale);
        $detail = [];
        if ($missingCount > 0) {
            $detail[] = $missingCount . ' missing';
        }
        if ($emptyCount > 0) {
            $detail[] = $emptyCount . ' empty';
        }
        $this->output->writeln($label . ' — ' . implode(', ', $detail));
    }

    /**
     * Count all base locale keys.
     *
     * @param TranslationReader $reader Translation reader.
     *
     * @return integer
     */
    private function countBaseKeys(TranslationReader $reader): int
    {
        $total = 0;
        foreach ($reader->getDomains() as $domain) {
            $total += count($reader->getKeys(TranslationReader::BASE_LOCALE, $domain));
        }
        return $total;
    }

    /**
     * Count missing keys in validation issues.
     *
     * @param array $issues Validation issues.
     *
     * @return integer
     */
    private function countMissing(array $issues): int
    {
        $count = 0;
        foreach ($issues as $domainIssues) {
            $count += count($domainIssues['missing'] ?? []);
        }
        return $count;
    }

    /**
     * Count empty values in validation issues.
     *
     * @param array $issues Validation issues.
     *
     * @return integer
     */
    private function countEmpty(array $issues): int
    {
        $count = 0;
        foreach ($issues as $domainIssues) {
            $count += count($domainIssues['empty'] ?? []);
        }
        return $count;
    }
}
