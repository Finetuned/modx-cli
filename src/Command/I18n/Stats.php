<?php

namespace MODX\CLI\Command\I18n;

use MODX\CLI\Command\BaseCmd;
use MODX\CLI\Translation\TranslationReader;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

/**
 * Show translation coverage statistics per locale and domain.
 */
class Stats extends BaseCmd
{
    public const MODX = false;

    protected $name = 'i18n:stats';
    protected $description = 'Show translation coverage statistics';

    protected function getArguments()
    {
        return [];
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['domain', 'd', InputOption::VALUE_REQUIRED, 'Limit to a specific domain'],
        ]);
    }

    protected function process()
    {
        $reader = TranslationReader::create();
        $domains = $this->filterDomains($reader->getDomains());
        $locales = $reader->getLocales();
        $stats = $this->buildStats($reader, $locales, $domains);

        if ($this->option('json')) {
            $this->output->writeln(json_encode($stats, JSON_PRETTY_PRINT));
            return 0;
        }

        $this->renderStats($stats);
        return 0;
    }

    private function filterDomains(array $domains): array
    {
        $filter = $this->option('domain');
        if ($filter === null) {
            return $domains;
        }
        return in_array($filter, $domains, true) ? [$filter] : [];
    }

    private function buildStats(TranslationReader $reader, array $locales, array $domains): array
    {
        $stats = [];
        $baseLocale = TranslationReader::BASE_LOCALE;

        foreach ($locales as $locale) {
            foreach ($domains as $domain) {
                $baseKeys = $reader->getKeys($baseLocale, $domain);
                $total = count($baseKeys);
                if ($total === 0) {
                    continue;
                }
                $localeKeys = $reader->getKeys($locale, $domain);
                $translated = count(array_intersect($localeKeys, $baseKeys));
                $stats[] = [
                    'locale'     => $locale,
                    'domain'     => $domain,
                    'total'      => $total,
                    'translated' => $translated,
                    'missing'    => $total - $translated,
                    'coverage'   => $total > 0 ? round(($translated / $total) * 100) : 100,
                ];
            }
        }

        return $stats;
    }

    private function renderStats(array $stats): void
    {
        $this->output->writeln('<info>Translation Coverage Statistics</info>');
        $this->output->writeln('');

        $table = new Table($this->output);
        $table->setHeaders(['Locale', 'Domain', 'Total', 'Translated', 'Missing', 'Coverage']);

        foreach ($stats as $row) {
            $coverage = $row['coverage'] . '%';
            if ($row['coverage'] === 100) {
                $coverage = '<info>' . $coverage . '</info>';
            } elseif ($row['coverage'] < 50) {
                $coverage = '<error>' . $coverage . '</error>';
            } else {
                $coverage = '<comment>' . $coverage . '</comment>';
            }
            $table->addRow([
                $row['locale'],
                $row['domain'],
                $row['total'],
                $row['translated'],
                $row['missing'],
                $coverage,
            ]);
        }

        $table->render();
    }
}
