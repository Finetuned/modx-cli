<?php

namespace MODX\CLI\Command\Snippet;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of snippets in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Element\Snippet\GetList';
    protected $headers = [
        'id', 'name', 'description', 'category', 'locked'
    ];

    protected $name = 'snippet:list';
    protected $description = 'Get a list of snippets in MODX';

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by category ID'
            ],
            [
                'search',
                null,
                InputOption::VALUE_REQUIRED,
                'Search term to filter results'
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
        // Add the category filter
        if ($this->option('category') !== null) {
            $properties['category'] = $this->option('category');
        }

        // Add the search term
        if ($this->option('search') !== null) {
            $properties['search'] = $this->option('search');
        }

        // Add pagination
        $properties['limit'] = $this->option('limit');
        $properties['start'] = $this->option('start');
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
        if ($column === 'category') {
            return $this->renderObject('modCategory', $value, 'category_name');
        }

        if ($column === 'locked') {
            return $value ? 'Yes' : 'No';
        }

        return parent::parseValue($value, $column);
    }
}
