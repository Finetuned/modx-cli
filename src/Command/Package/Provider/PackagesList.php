<?php

namespace MODX\CLI\Command\Package\Provider;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of packages from a provider in MODX
 */
class PackagesList extends ListProcessor
{
    protected $processor = 'Workspace\Packages\Rest\GetList';
    protected $required = ['provider'];
    protected $headers = [
        'signature', 'name', 'version', 'release', 'installed'
    ];

    protected $name = 'package:provider:packages';
    protected $description = 'Get a list of packages from a provider in MODX';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'provider',
                InputArgument::REQUIRED,
                'The ID of the provider'
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'query',
                null,
                InputOption::VALUE_REQUIRED,
                'Search query'
            ],
            [
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by category'
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return void
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Add filters based on options
        if ($this->option('query') !== null) {
            $properties['query'] = $this->option('query');
        }
        if ($this->option('category') !== null) {
            $properties['category'] = $this->option('category');
        }
    }

    /**
     * Format raw values for output.
     *
     * @param mixed  $value  The raw column value.
     * @param string $column The column name.
     * @return mixed
     */
    protected function parseValue(mixed $value, string $column)
    {
        if ($column === 'installed') {
            return $value ? 'Yes' : 'No';
        }

        return parent::parseValue($value, $column);
    }
}
