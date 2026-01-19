<?php

namespace MODX\CLI\Command\User;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of users in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'Security\User\GetList';
    protected $headers = [
        'id', 'username', 'fullname', 'email', 'active'
    ];

    protected $name = 'user:list';
    protected $description = 'Get a list of users in MODX';

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'active',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by active status (1 or 0)'
            ],
            [
                'blocked',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by blocked status (1 or 0)'
            ],
            [
                'usergroup',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by user group ID'
            ],
            [
                'query',
                null,
                InputOption::VALUE_REQUIRED,
                'Search query'
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
        // Add filters based on options
        if ($this->option('active') !== null) {
            $properties['active'] = $this->option('active');
        }
        if ($this->option('blocked') !== null) {
            $properties['blocked'] = $this->option('blocked');
        }
        if ($this->option('usergroup') !== null) {
            $properties['usergroup'] = $this->option('usergroup');
        }
        if ($this->option('query') !== null) {
            $properties['query'] = $this->option('query');
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
        if ($column === 'active') {
            return $this->renderBoolean($value);
        }

        return parent::parseValue($value, $column);
    }
}
