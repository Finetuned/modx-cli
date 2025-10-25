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
    protected $headers = array(
        'id', 'action', 'name', 'occurred'
    );

    protected $name = 'system:log:view';
    protected $description = 'View the MODX system log';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'level',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter by log level (ERROR, WARN, INFO, DEBUG)'
            ),
            array(
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format (table, colored)',
                'table'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add filters based on options
        if ($this->option('level') !== null) {
            $properties['level'] = $this->option('level');
        }
    }

    protected function processResponse(array $results = array())
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
     * Render the log entries with colors
     *
     * @param array $logs
     */
    protected function renderColored(array $logs = array())
    {
        if (count($logs) === 0) {
            $this->info('No log entries found');
            return;
        }

        $formatter = new ColoredLog();
        $entries = array();

        foreach ($logs as $log) {
            $entries[] = array(
                'level' => $log['action'],
                'message' => $log['name'],
                'timestamp' => strtotime($log['occurred']),
            );
        }

        // Sort by timestamp
        usort($entries, function ($a, $b) {
            return $a['timestamp'] - $b['timestamp'];
        });

        $this->output->write($formatter->formatMultiple($entries));
    }

    protected function parseValue($value, $column)
    {
        if ($column === 'action') {
            return strtoupper($value);
        }

        return parent::parseValue($value, $column);
    }
}
