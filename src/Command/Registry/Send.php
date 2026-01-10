<?php

namespace MODX\CLI\Command\Registry;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to send a message to a MODX registry register
 */
class Send extends ProcessorCmd
{
    protected $processor = 'System\\Registry\\Register\\Send';

    protected $name = 'registry:send';
    protected $description = 'Send a message to a MODX registry register';

    protected $required = array('topic', 'message');

    protected function getArguments()
    {
        return array(
            array(
                'topic',
                InputArgument::REQUIRED,
                'The topic to send to'
            ),
            array(
                'message',
                InputArgument::REQUIRED,
                'The message to send'
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
                'register_class',
                null,
                InputOption::VALUE_REQUIRED,
                'Custom registry class (optional)'
            ),
            array(
                'message_key',
                null,
                InputOption::VALUE_REQUIRED,
                'Optional message key'
            ),
            array(
                'message_format',
                null,
                InputOption::VALUE_REQUIRED,
                'Message format (string, json)',
                'string'
            ),
            array(
                'delay',
                null,
                InputOption::VALUE_REQUIRED,
                'Delay in seconds',
                0
            ),
            array(
                'ttl',
                null,
                InputOption::VALUE_REQUIRED,
                'Time-to-live in seconds',
                0
            ),
            array(
                'kill',
                null,
                InputOption::VALUE_NONE,
                'Kill the register after sending'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $properties['register'] = $this->option('register');
        $properties['message_format'] = $this->option('message_format');
        $properties['delay'] = (int) $this->option('delay');
        $properties['ttl'] = (int) $this->option('ttl');
        $properties['kill'] = (bool) $this->option('kill');

        if ($this->option('register_class') !== null) {
            $properties['register_class'] = $this->option('register_class');
        }

        if ($this->option('message_key') !== null) {
            $properties['message_key'] = $this->option('message_key');
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            $this->output->writeln(json_encode($response, JSON_PRETTY_PRINT));
            return 0;
        }

        if (!empty($response['success'])) {
            $this->info('Registry message sent successfully');
        } else {
            $this->error('Failed to send registry message');
        }

        return 0;
    }
}
