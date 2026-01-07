<?php

namespace MODX\CLI\Command\Context;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to get a single MODX context
 */
class Get extends ProcessorCmd
{
    protected $processor = 'Context\Get';

    protected $name = 'context:get';
    protected $description = 'Get a MODX context by key';

    protected function getArguments()
    {
        return array(
            array(
                'key',
                InputArgument::REQUIRED,
                'The context key'
            ),
        );
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $properties['key'] = $this->argument('key');
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            if (isset($response['object'])) {
                $context = $response['object'];
                $this->info('Context: ' . $context['key']);
                $this->info('Name: ' . ($context['name'] ?? ''));
                $this->info('Description: ' . ($context['description'] ?? ''));
                $this->info('Rank: ' . ($context['rank'] ?? 0));
            }
            return 0;
        } else {
            $this->error('Failed to get context');
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}