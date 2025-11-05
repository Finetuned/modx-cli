<?php

namespace MODX\CLI\Command\Chunk;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of chunks in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Element\Chunk\GetList';
    protected $headers = array(
        'id', 'name', 'description', 'category'
    );

    protected $name = 'chunk:list';
    protected $description = 'Get a list of chunks in MODX';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by category ID'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the category filter
        if ($this->option('category') !== null) {
            $properties['category'] = $this->option('category');
        }

        return parent::beforeRun($properties, $options);
    }

    protected function parseValue($value, $column)
    {
        if ($column === 'category') {
            return $this->renderObject('modCategory', $value, 'category');
        }

        return parent::parseValue($value, $column);
    }
}
