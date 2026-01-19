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

    protected $required = ['topic'];

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
                'The topic to read from'
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
                'Registry name to use',
                'db'
            ],
            [
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Output format (json, html_log, raw)',
                'json'
            ],
            [
                'register_class',
                null,
                InputOption::VALUE_REQUIRED,
                'Custom registry class (optional)'
            ],
            [
                'poll_limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Number of poll cycles',
                1
            ],
            [
                'poll_interval',
                null,
                InputOption::VALUE_REQUIRED,
                'Interval between polls',
                1
            ],
            [
                'time_limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Time limit for polling',
                10
            ],
            [
                'message_limit',
                null,
                InputOption::VALUE_REQUIRED,
                'Maximum messages to read',
                200
            ],
            [
                'keep',
                null,
                InputOption::VALUE_NONE,
                'Keep messages after reading'
            ],
            [
                'include_keys',
                null,
                InputOption::VALUE_NONE,
                'Include message keys in the output'
            ],
            [
                'show_filename',
                null,
                InputOption::VALUE_NONE,
                'Include message filename metadata'
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

    /**
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
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
