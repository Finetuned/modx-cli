<?php namespace MODX\CLI\Command\Registry\Message;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of registry messages in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'registry/message/getlist';
    protected $required = array('topic');
    protected $headers = array(
        'id', 'topic', 'message', 'created'
    );

    protected $name = 'registry:message:list';
    protected $description = 'Get a list of registry messages in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'topic',
                InputArgument::REQUIRED,
                'The topic of the messages'
            ),
        );
    }

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
        // Add the topic to the properties
        $properties['topic'] = $this->argument('topic');
        
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
