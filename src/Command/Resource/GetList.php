<?php

namespace MODX\CLI\Command\Resource;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of resources
 */
class GetList extends ListProcessor
{
    protected $processor = 'resource/getlist';
    protected $headers = array(
        'id', 'pagetitle', 'alias', 'published', 'hidemenu', 'context_key'
    );

    protected $name = 'resource:list';
    protected $description = 'Get a list of resources';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by parent ID'
            ),
            array(
                'context',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by context key'
            ),
            array(
                'published',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by published status (1 or 0)'
            ),
            array(
                'hidemenu',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by hidemenu status (1 or 0)'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
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

    protected function parseValue($value, $column)
    {
        if ($column === 'published' || $column === 'hidemenu') {
            return $this->renderBoolean($value);
        }
        return parent::parseValue($value, $column);
    }
}
