<?php

namespace MODX\CLI\Command\Snippet;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of snippets in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'element/snippet/getlist';
    protected $headers = array(
        'id', 'name', 'description', 'category', 'locked'
    );

    protected $name = 'snippet:list';
    protected $description = 'Get a list of snippets in MODX';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by category ID'
            ),
            array(
                'search',
                null,
                InputOption::VALUE_REQUIRED,
                'Search term to filter results'
            ),
            array(
                'limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Limit the number of results',
                20
            ),
            array(
                'start',
                null,
                InputOption::VALUE_REQUIRED,
                'Start index for pagination',
                0
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
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

    protected function parseValue($value, $column)
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
