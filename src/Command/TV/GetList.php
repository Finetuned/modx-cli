<?php

namespace MODX\CLI\Command\TV;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of template variables in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Element\Tv\GetList';
    protected $headers = [
        'id', 'name', 'caption', 'description', 'category', 'type'
    ];

    protected $name = 'tv:list';
    protected $description = 'Get a list of template variables in MODX';

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
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Add the category filter
        if ($this->option('category') !== null) {
            $properties['category'] = $this->option('category');
        }

        return parent::beforeRun($properties, $options);
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
            return $this->renderObject('modCategory', $value, 'category');
        }

        return parent::parseValue($value, $column);
    }
}
