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
    protected $headers = [
        'id', 'name', 'created'
    ];

    protected $name = 'registry:queue:list';
    protected $description = 'Get a list of registry queues in MODX';

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
            [
                'topic',
                null,
                InputOption::VALUE_REQUIRED,
                'The topic to filter by'
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
        // Add the register to the properties
        if ($this->option('register') !== null) {
            $properties['register'] = $this->option('register');
        }

        // Add the topic to the properties
        if ($this->option('topic') !== null) {
            $properties['topic'] = $this->option('topic');
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
