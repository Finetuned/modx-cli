<?php

namespace MODX\CLI\Command\Template;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of templates in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Element\Template\GetList';
    protected $headers = array(
        'id', 'templatename', 'description', 'category'
    );

    protected $name = 'template:list';
    protected $description = 'Get a list of templates in MODX';

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
