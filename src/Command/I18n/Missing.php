<?php

namespace MODX\CLI\Command\I18n;

use MODX\CLI\Command\BaseCmd;
use MODX\CLI\Translation\TranslationReader;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * List translation keys present in the base locale but missing in a target locale.
 *
 * Exit code 1 if any keys are missing (CI-friendly).
 */
class Missing extends BaseCmd
{
    public const MODX = false;

    protected $name = 'i18n:missing';
    protected $description = 'List missing translation keys for a locale';

    /**
     * Get command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['locale', InputArgument::REQUIRED, 'Target locale to check (e.g. fr, de, ru)'],
        ];
    }

    /**
     * Get command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['domain', 'd', InputOption::VALUE_REQUIRED, 'Limit to a specific domain'],
        ]);
    }

    /**
     * Collect and render missing keys for the target locale.
     *
     * @return integer
     */
    protected function process()
    {
        $reader  = TranslationReader::create();
        $locale  = $this->argument('locale');
        $domains = $this->filterDomains($reader->getDomains());

        $missing = $this->collectMissing($reader, $locale, $domains);
        $total   = array_sum(array_map('count', $missing));

        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'locale'  => $locale,
                'total'   => $total,
                'missing' => $missing,
            ], JSON_PRETTY_PRINT));
            return $total > 0 ? 1 : 0;
        }

        $this->renderMissing($locale, $missing, $total);
        return $total > 0 ? 1 : 0;
    }

    /**
     * Filter translation domains by the command option.
     *
     * @param array $domains Domain names.
     *
     * @return array
     */
    private function filterDomains(array $domains): array
    {
        $filter = $this->option('domain');
        if ($filter === null) {
            return $domains;
        }
        return in_array($filter, $domains, true) ? [$filter] : [];
    }

    /**
     * Collect missing keys for all selected domains.
     *
     * @param TranslationReader $reader  Translation reader.
     * @param string            $locale  Locale name.
     * @param array             $domains Domain names.
     *
     * @return array
     */
    private function collectMissing(TranslationReader $reader, string $locale, array $domains): array
    {
        $result = [];
        foreach ($domains as $domain) {
            $keys = $reader->getMissingKeys($locale, $domain);
            if ($keys !== []) {
                $result[$domain] = $keys;
            }
        }
        return $result;
    }

    /**
     * Render missing keys for one locale.
     *
     * @param string  $locale  Locale name.
     * @param array   $missing Missing keys by domain.
     * @param integer $total   Total missing keys.
     *
     * @return void
     */
    private function renderMissing(string $locale, array $missing, int $total): void
    {
        if ($total === 0) {
            $this->output->writeln(sprintf('<info>No missing keys for locale "%s".</info>', $locale));
            return;
        }

        $this->output->writeln(sprintf(
            '<comment>Missing keys for locale "%s" (%d total):</comment>',
            $locale,
            $total
        ));

        foreach ($missing as $domain => $keys) {
            $this->output->writeln('');
            $this->output->writeln(sprintf('  <info>[%s]</info>', $domain));
            foreach ($keys as $key) {
                $this->output->writeln('    ' . $key);
            }
        }
    }
}
