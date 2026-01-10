<?php

namespace MODX\CLI\Command\Registry;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to read from a MODX registry register
 */
class Read extends ProcessorCmd
{
    protected $processor = 'System\\Registry\\Register\\Read';

    protected $name = 'registry:read';
    protected $description = 'Read messages from a MODX registry register';

    protected $required = array('topic');

    protected function getArguments()
    {
        return array(
            array(
                'topic',
                InputArgument::REQUIRED,
                'The topic to read from'
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
                'Registry name to use',
                'db'
            ),
            array(
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Output format (json, html_log, raw)',
                'json'
            ),
            array(
                'register_class',
                null,
                InputOption::VALUE_REQUIRED,
                'Custom registry class (optional)'
            ),
            array(
                'poll_limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of poll cycles',
                1
            ),
            array(
                'poll_interval',
                null,
                InputOption::VALUE_REQUIRED,
                'Interval between polls',
                1
            ),
            array(
                'time_limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Time limit for polling',
                10
            ),
            array(
                'message_limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Maximum messages to read',
                200
            ),
            array(
                'keep',
                null,
                InputOption::VALUE_NONE,
                'Keep messages after reading'
            ),
            array(
                'include_keys',
                null,
                InputOption::VALUE_NONE,
                'Include message keys in the output'
            ),
            array(
                'show_filename',
                null,
                InputOption::VALUE_NONE,
                'Include message filename metadata'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $properties['register'] = $this->option('register');
        $properties['format'] = $this->option('format');

        if ($this->option('register_class') !== null) {
            $properties['register_class'] = $this->option('register_class');
        }

        $properties['poll_limit'] = (int) $this->option('poll_limit');
        $properties['poll_interval'] = (int) $this->option('poll_interval');
        $properties['time_limit'] = (int) $this->option('time_limit');
        $properties['message_limit'] = (int) $this->option('message_limit');
        $properties['remove_read'] = !$this->option('keep');
        $properties['include_keys'] = (bool) $this->option('include_keys');
        $properties['show_filename'] = (bool) $this->option('show_filename');
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            if (isset($response['message']) && $this->option('format') === 'json') {
                $decoded = json_decode($response['message'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $response['message'] = $decoded;
                }
            }

            $this->output->writeln(json_encode($response, JSON_PRETTY_PRINT));
            return 0;
        }

        if (!empty($response['message'])) {
            $this->output->writeln($response['message']);
            return 0;
        }

        $this->info('No registry messages found');
        return 0;
    }
}
