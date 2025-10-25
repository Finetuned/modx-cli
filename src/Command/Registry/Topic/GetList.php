<?php

namespace MODX\CLI\Command\Registry\Topic;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of registry topics in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Registry\Topic\GetList';
    protected $headers = array(
        'id', 'name', 'created'
    );

    protected $name = 'registry:topic:list';
    protected $description = 'Get a list of registry topics in MODX';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'register',
                null,
                InputOption::VALUE_REQUIRED,
                'The register to use',
                'db'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the register to the properties
        if ($this->option('register') !== null) {
            $properties['register'] = $this->option('register');
        }
    }

    protected function parseValue($value, $column)
    {
        if ($column === 'created') {
            return date('Y-m-d H:i:s', strtotime($value));
        }

        return parent::parseValue($value, $column);
    }
}
