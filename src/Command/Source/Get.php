<?php

namespace MODX\CLI\Command\Source;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to get a single MODX media source
 */
class Get extends ProcessorCmd
{
    protected $processor = 'Source\Get';

    protected $name = 'source:get';
    protected $description = 'Get a MODX media source by ID';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The source ID'
            ),
        );
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $properties['id'] = $this->argument('id');
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            if (isset($response['object'])) {
                $source = $response['object'];
                $this->info('ID: ' . ($source['id'] ?? ''));
                $this->info('Name: ' . ($source['name'] ?? ''));
                $this->info('Description: ' . ($source['description'] ?? ''));
                $this->info('Class Key: ' . ($source['class_key'] ?? ''));
            }
            return 0;
        } else {
            $this->error('Failed to get media source');
            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}