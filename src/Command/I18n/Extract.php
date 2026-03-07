<?php

namespace MODX\CLI\Command\I18n;

use MODX\CLI\Command\BaseCmd;
use MODX\CLI\Translation\TranslationReader;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Scan PHP source files for trans() calls and compare against YAML translation files.
 *
 * Reports keys that exist in code but not in YAML (new) and vice versa (orphaned).
 * With --update, writes new keys to the base (en) YAML files.
 */
class Extract extends BaseCmd
{
    public const MODX = false;

    protected $name = 'i18n:extract';
    protected $description = 'Extract translation keys from source code and compare with YAML files';

    /** @var string[] Regex patterns for trans()/transChoice() calls */
    private const PATTERN_TRANS = '/->trans(?:Choice)?\s*\(\s*[\'"]([^\'"]+)[\'"]\s*(?:,[^,]+,\s*[\'"]([^\'"]+)[\'"])?\s*[,\)]/';

    /** @var string Regex pattern for ErrorMessages::get/format/has() calls */
    private const PATTERN_ERROR_MESSAGES = '/ErrorMessages::(?:get|format|has)\s*\(\s*[\'"]([^\'"]+)[\'"]/';

    protected function getArguments()
    {
        return [];
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['update', null, InputOption::VALUE_NONE, 'Write new keys to the base (en) YAML files'],
            ['domain', 'd', InputOption::VALUE_REQUIRED, 'Limit to a specific domain'],
        ]);
    }

    protected function process()
    {
        $reader  = TranslationReader::create();
        $domains = $this->filterDomains($reader->getDomains());

        $found   = $this->scanSourceFiles();
        $report  = $this->buildReport($reader, $found, $domains);

        if ($this->option('json')) {
            $this->output->writeln(json_encode($report, JSON_PRETTY_PRINT));
        } else {
            $this->renderReport($report);
        }

        if ($this->option('update')) {
            $this->writeNewKeys($reader, $report);
        }

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

    /**
     * Scans all PHP files under src/ and returns [domain => [key, ...]] map.
     */
    private function scanSourceFiles(): array
    {
        $srcPath = realpath(__DIR__ . '/../../../src') ?: __DIR__ . '/../../..';
        $finder  = new Finder();
        $finder->files()->name('*.php')->in($srcPath);

        $found = [];
        foreach ($finder as $file) {
            $content = $file->getContents();
            $found   = $this->extractFromContent($content, $found);
        }

        return $found;
    }

    /**
     * Applies both regex patterns to file content and accumulates keys.
     */
    private function extractFromContent(string $content, array $found): array
    {
        if (preg_match_all(self::PATTERN_TRANS, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $key    = $match[1];
                $domain = isset($match[2]) && $match[2] !== '' ? $match[2] : 'messages';
                $found[$domain][$key] = true;
            }
        }

        if (preg_match_all(self::PATTERN_ERROR_MESSAGES, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $found['errors'][$match[1]] = true;
            }
        }

        return $found;
    }

    /**
     * Compares found keys with YAML keys and builds the report structure.
     */
    private function buildReport(TranslationReader $reader, array $found, array $domains): array
    {
        $report = [];
        foreach ($domains as $domain) {
            $yamlKeys  = $reader->getKeys(TranslationReader::BASE_LOCALE, $domain);
            $codeKeys  = array_keys($found[$domain] ?? []);
            $report[$domain] = [
                'new'      => array_values(array_diff($codeKeys, $yamlKeys)),
                'orphaned' => array_values(array_diff($yamlKeys, $codeKeys)),
            ];
        }
        return $report;
    }

    private function renderReport(array $report): void
    {
        $hasAny = false;
        foreach ($report as $domain => $diff) {
            $hasAny = $this->renderDomainDiff($domain, $diff) || $hasAny;
        }
        if (!$hasAny) {
            $this->output->writeln('<info>All translation keys are in sync.</info>');
        }
    }

    private function renderDomainDiff(string $domain, array $diff): bool
    {
        $hasOutput = false;
        if ($diff['new'] !== []) {
            $this->output->writeln(sprintf('<comment>[%s] New keys (in code, not in YAML):</comment>', $domain));
            foreach ($diff['new'] as $key) {
                $this->output->writeln('  + ' . $key);
            }
            $hasOutput = true;
        }
        if ($diff['orphaned'] !== []) {
            $this->output->writeln(sprintf('<comment>[%s] Orphaned keys (in YAML, not in code):</comment>', $domain));
            foreach ($diff['orphaned'] as $key) {
                $this->output->writeln('  - ' . $key);
            }
            $hasOutput = true;
        }
        return $hasOutput;
    }

    /**
     * Writes new (in-code, not-in-YAML) keys to the base en/ YAML files.
     */
    private function writeNewKeys(TranslationReader $reader, array $report): void
    {
        foreach ($report as $domain => $diff) {
            if ($diff['new'] === []) {
                continue;
            }
            $this->writeDomainKeys($reader, $domain, $diff['new']);
        }
    }

    private function writeDomainKeys(TranslationReader $reader, string $domain, array $newKeys): void
    {
        $filePath = $reader->getFilePath(TranslationReader::BASE_LOCALE, $domain);
        $existing = file_exists($filePath) ? (Yaml::parseFile($filePath) ?? []) : [];

        $additions = array_fill_keys($newKeys, '');
        $merged    = array_merge_recursive($existing, $reader->unflatten($additions));

        file_put_contents($filePath, Yaml::dump($merged, 10, 2));

        $this->output->writeln(sprintf(
            '<info>Added %d key(s) to %s/%s.yaml</info>',
            count($newKeys),
            TranslationReader::BASE_LOCALE,
            $domain
        ));
    }
}
