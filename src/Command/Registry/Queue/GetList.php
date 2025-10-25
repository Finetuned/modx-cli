<?php

namespace MODX\CLI\Command\Registry\Queue;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of registry queues in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Registry\Queue\GetList';
    protected $headers = array(
        'id', 'name', 'created'
    );

    protected $name = 'registry:queue:list';
    protected $description = 'Get a list of registry queues in MODX';

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
            array(
                'topic',
                null,
                InputOption::VALUE_REQUIRED,
                'The topic to filter by'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the register to the properties
        if ($this->option('register') !== null) {
            $properties['register'] = $this->option('register');
        }

        // Add the topic to the properties
        if ($this->option('topic') !== null) {
            $properties['topic'] = $this->option('topic');
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
