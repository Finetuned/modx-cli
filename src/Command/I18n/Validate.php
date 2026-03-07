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

    protected function getArguments()
    {
        return [];
    }

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

    private function resolveLocales(TranslationReader $reader): array
    {
        $filter = $this->option('locale');
        if ($filter !== null) {
            return [$filter];
        }
        return $reader->getLocales();
    }

    private function validate(TranslationReader $reader, array $locales, array $domains): array
    {
        $results = [];
        foreach ($locales as $locale) {
            $results[$locale] = $this->validateLocale($reader, $locale, $domains);
        }
        return $results;
    }

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

    private function hasIssues(array $results): bool
    {
        foreach ($results as $localeIssues) {
            if ($localeIssues !== []) {
                return true;
            }
        }
        return false;
    }

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

    private function countBaseKeys(TranslationReader $reader): int
    {
        $total = 0;
        foreach ($reader->getDomains() as $domain) {
            $total += count($reader->getKeys(TranslationReader::BASE_LOCALE, $domain));
        }
        return $total;
    }

    private function countMissing(array $issues): int
    {
        $count = 0;
        foreach ($issues as $domainIssues) {
            $count += count($domainIssues['missing'] ?? []);
        }
        return $count;
    }

    private function countEmpty(array $issues): int
    {
        $count = 0;
        foreach ($issues as $domainIssues) {
            $count += count($domainIssues['empty'] ?? []);
        }
        return $count;
    }
}
