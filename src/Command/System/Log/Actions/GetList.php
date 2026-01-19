<?php

namespace MODX\CLI\Command\System\Log\Actions;

use MODX\CLI\Command\ListProcessor;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to get a list of action logs in MODX
 */
class GetList extends ListProcessor
{
    protected $processor = 'System\Log\GetList';
    protected $headers = [
        'id', 'user', 'action', 'classKey', 'item', 'occurred'
    ];

    protected $name = 'system:log:actions:list';
    protected $description = 'Get a list of action logs in MODX';

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'user',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by user ID'
            ],
            [
                'action',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by action'
            ],
            [
                'classKey',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by class key'
            ],
            [
                'item',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by item'
            ],
            [
                'dateStart',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by start date (YYYY-MM-DD)'
            ],
            [
                'dateEnd',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by end date (YYYY-MM-DD)'
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Add filters based on options
        $optionKeys = ['user', 'action', 'classKey', 'item', 'dateStart', 'dateEnd'];

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }

        // Call parent to handle pagination
        return parent::beforeRun($properties, $options);
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
        if ($column === 'occurred') {
            return date('Y-m-d H:i:s', strtotime($value));
        }

        if ($column === 'user') {
            return $this->renderObject('modUser', $value, 'username');
        }

        return parent::parseValue($value, $column);
    }
}
