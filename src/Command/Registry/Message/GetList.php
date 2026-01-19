<?php

namespace MODX\CLI\Command\Registry\Message;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of registry messages in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Registry\Message\GetList';
    protected $required = ['topic'];
    protected $headers = [
        'id', 'topic', 'message', 'created'
    ];

    protected $name = 'registry:message:list';
    protected $description = 'Get a list of registry messages in MODX';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'topic',
                InputArgument::REQUIRED,
                'The topic of the messages'
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'register',
                null,
                InputOption::VALUE_REQUIRED,
                'The register to use',
                'db'
            ],
        ]);
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
        // Add the topic to the properties
        $properties['topic'] = $this->argument('topic');

        // Add the register to the properties
        if ($this->option('register') !== null) {
            $properties['register'] = $this->option('register');
        }
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
        if ($column === 'created') {
            return date('Y-m-d H:i:s', strtotime($value));
        }

        return parent::parseValue($value, $column);
    }
}
