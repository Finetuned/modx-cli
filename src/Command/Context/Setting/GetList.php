<?php namespace MODX\CLI\Command\Context\Setting;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to get a list of context settings in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'context/setting/getlist';
    protected $required = array('context_key');
    protected $headers = array(
        'key', 'value', 'name', 'description'
    );

    protected $name = 'context:setting:list';
    protected $description = 'Get a list of context settings in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'context_key',
                InputArgument::REQUIRED,
                'The context key'
            ),
        );
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the context_key to the properties
        $properties['context_key'] = $this->argument('context_key');
    }
}
