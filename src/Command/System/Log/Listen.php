<?php

namespace MODX\CLI\Command\System\Log;

use MODX\CLI\Command\BaseCmd;
use MODX\CLI\Formatter\ColoredLog;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to listen to the MODX system log
 */
class Listen extends BaseCmd
{
    const MODX = true;

    protected $name = 'system:log:listen';
    protected $description = 'Listen to the MODX system log';

    /**
     * @var int
     */
    protected $lastLogId = 0;

    /**
     * @var int
     */
    protected $interval = 1;

    /**
     * @var bool
     */
    protected $running = true;

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'interval',
                'i',
                InputOption::VALUE_REQUIRED,
                'Interval in seconds between checks',
                1
            ),
            array(
                'limit',
                'l',
                InputOption::VALUE_REQUIRED,
                'Number of log entries to display initially',
                10
            ),
        ));
    }

    protected function process()
    {
        $this->interval = (int) $this->option('interval');
        $limit = (int) $this->option('limit');
        $json = $this->isJsonOutput();

        // Get the last log ID
        $this->lastLogId = $this->getLastLogId();

        // Display the last N log entries
        $this->displayLastLogEntries($limit);

        // Listen for new log entries
        if (!$json) {
            $this->info('Listening for new log entries (Ctrl+C to stop)...');
        }
        while ($this->running) {
            $this->checkForNewLogEntries();
            sleep($this->interval);
        }

        return 0;
    }

    /**
     * Get the last log ID
     *
     * @return int
     */
    protected function getLastLogId()
    {
        $c = $this->modx->newQuery(\MODX\Revolution\modManagerLog::class);
        $c->sortby('id', 'DESC');
        $c->limit(1);

        /** @var \MODX\Revolution\modManagerLog|null $log */
        $log = $this->modx->getObject(\MODX\Revolution\modManagerLog::class, $c);

        return $log ? $log->get('id') : 0;
    }

    /**
     * Display the last N log entries
     *
     * @param int $limit
     */
    protected function displayLastLogEntries($limit)
    {
        $json = $this->isJsonOutput();
        $c = $this->modx->newQuery(\MODX\Revolution\modManagerLog::class);
        $c->sortby('id', 'DESC');
        $c->limit($limit);

        $logs = $this->modx->getCollection(\MODX\Revolution\modManagerLog::class, $c);

        if (!$logs || count($logs) === 0) {
            if ($json) {
                $this->output->writeln(json_encode([
                    'total' => 0,
                    'results' => [],
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info('No log entries found');
            }
            return;
        }

        $entries = array();

        /** @var \MODX\Revolution\modManagerLog $log */
        foreach ($logs as $log) {
            $entries[] = array(
                'id' => $log->get('id'),
                'level' => $log->get('level'),
                'message' => $log->get('message'),
                'timestamp' => strtotime($log->get('occurred')),
            );

            $this->lastLogId = max($this->lastLogId, $log->get('id'));
        }

        // Sort by timestamp
        usort($entries, function ($a, $b) {
            return $a['timestamp'] - $b['timestamp'];
        });

        if ($json) {
            $this->output->writeln(json_encode([
                'total' => count($entries),
                'results' => $entries,
            ], JSON_PRETTY_PRINT));
        } else {
            $formatter = new ColoredLog();
            $this->output->write($formatter->formatMultiple($entries));
        }
    }

    /**
     * Check for new log entries
     */
    protected function checkForNewLogEntries()
    {
        $json = $this->isJsonOutput();
        $c = $this->modx->newQuery(\MODX\Revolution\modManagerLog::class);
        $c->where(array(
            'id:>' => $this->lastLogId,
        ));
        $c->sortby('id', 'ASC');

        $logs = $this->modx->getCollection(\MODX\Revolution\modManagerLog::class, $c);

        if (!$logs || count($logs) === 0) {
            return;
        }

        $entries = array();

        /** @var \MODX\Revolution\modManagerLog $log */
        foreach ($logs as $log) {
            $entries[] = array(
                'level' => $log->get('level'),
                'message' => $log->get('message'),
                'timestamp' => strtotime($log->get('occurred')),
            );

            $this->lastLogId = max($this->lastLogId, $log->get('id'));
        }

        if ($json) {
            $this->output->writeln(json_encode([
                'total' => count($entries),
                'results' => $entries,
            ], JSON_PRETTY_PRINT));
        } else {
            $formatter = new ColoredLog();
            $this->output->write($formatter->formatMultiple($entries));
        }
    }

    protected function isJsonOutput(): bool
    {
        return $this->input ? (bool) $this->option('json') : false;
    }
}
