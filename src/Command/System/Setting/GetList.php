<?php

namespace MODX\CLI\Command\System\Setting;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of system settings in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'System\Settings\GetList';
    protected $headers = [
        'key', 'value', 'name', 'description', 'area'
    ];

    protected $name = 'system:setting:list';
    protected $description = 'Get a list of system settings in MODX';

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'area',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by area'
            ],
            [
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by namespace'
            ],
            [
                'query',
                null,
                InputOption::VALUE_REQUIRED,
                'Search query'
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
        if ($this->option('area') !== null) {
            $properties['area'] = $this->option('area');
        }
        if ($this->option('namespace') !== null) {
            $properties['namespace'] = $this->option('namespace');
        }
        if ($this->option('query') !== null) {
            $properties['query'] = $this->option('query');
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
        if ($column === 'area') {
            return $this->renderObject('modNamespace', $value, 'name');
        }

        return parent::parseValue($value, $column);
    }
}
