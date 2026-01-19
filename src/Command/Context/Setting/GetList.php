<?php

namespace MODX\CLI\Command\Context\Setting;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to get a list of context settings in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Context\Setting\GetList';
    protected $required = ['context_key'];
    protected $headers = [
        'key', 'value', 'name', 'description'
    ];

    protected $name = 'context:setting:list';
    protected $description = 'Get a list of context settings in MODX';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'context_key',
                InputArgument::REQUIRED,
                'The context key'
            ],
        ];
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
        // Add the context_key to the properties
        $properties['context_key'] = $this->argument('context_key');
    }
}
