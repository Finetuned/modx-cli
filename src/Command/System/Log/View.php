<?php

namespace MODX\CLI\Command\System\Log;

use MODX\CLI\Command\ListProcessor;
use MODX\CLI\Formatter\ColoredLog;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to view the MODX system log
 */
class View extends ListProcessor
{
    protected $processor = 'System\Log\GetList';
    protected $headers = [
        'id', 'action', 'name', 'occurred'
    ];

    protected $name = 'system:log:view';
    protected $description = 'View the MODX system log';

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'level',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by log level (ERROR, WARN, INFO, DEBUG)'
            ],
            [
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format (table, colored)',
                'table'
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
        if ($this->option('level') !== null) {
            $properties['level'] = $this->option('level');
        }
    }

    /**
     * Handle the processor response.
     *
     * @param array $results The processor response.
     * @return integer
     */
    protected function processResponse(array $results = [])
    {
        $format = $this->option('format');

        if ($format === 'colored') {
            $this->renderColored($results['results']);
            return 0; // Return 0 for success
        } else {
            return parent::processResponse($results);
        }
    }

    /**
     * Render the log entries with colors.
     *
     * @param array $logs The log entries.
     * @return void
     */
    protected function renderColored(array $logs = []): void
    {
        if (count($logs) === 0) {
            $this->info('No log entries found');
            return;
        }

        $formatter = new ColoredLog();
        $entries = [];

        foreach ($logs as $log) {
            $entries[] = [
                'level' => $log['action'],
                'message' => $log['name'],
                'timestamp' => strtotime($log['occurred']),
            ];
        }

        // Sort by timestamp
        usort($entries, function ($a, $b) {
            return $a['timestamp'] - $b['timestamp'];
        });

        $this->output->write($formatter->formatMultiple($entries));
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
        if ($column === 'action') {
            return strtoupper($value);
        }

        return parent::parseValue($value, $column);
    }
}
