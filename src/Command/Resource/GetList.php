<?php

namespace MODX\CLI\Command\Resource;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of resources
 */
class GetList extends ListProcessor
{
    protected $processor = 'Resource\GetList';
    protected $headers = [
        'id', 'pagetitle', 'alias', 'published', 'hidemenu', 'context_key'
    ];

    protected $name = 'resource:list';
    protected $description = 'Get a list of resources';

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by parent ID'
            ],
            [
                'context',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by context key'
            ],
            [
                'published',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by published status (1 or 0)'
            ],
            [
                'hidemenu',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by hidemenu status (1 or 0)'
            ],
        ]);
    }

    /**
     * Prepare processor properties before execution.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return void
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Add filters based on options
        if ($this->option('parent') !== null) {
            $properties['parent'] = $this->option('parent');
        }
        if ($this->option('context') !== null) {
            $properties['context'] = $this->option('context');
        }
        if ($this->option('published') !== null) {
            $properties['published'] = $this->option('published');
        }
        if ($this->option('hidemenu') !== null) {
            $properties['hidemenu'] = $this->option('hidemenu');
        }
    }

    /**
     * Parse column values for display.
     *
     * @param mixed  $value  The column value.
     * @param string $column The column name.
     * @return mixed
     */
    protected function parseValue(mixed $value, string $column)
    {
        if ($column === 'published' || $column === 'hidemenu') {
            return $this->renderBoolean($value);
        }
        return parent::parseValue($value, $column);
    }
}
