<?php

namespace MODX\CLI\Command\Chunk;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX chunk
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Element\Chunk\Create';

    protected $name = 'chunk:create';
    protected $description = 'Create a MODX chunk';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the chunk'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the chunk',
                ''
            ),
            array(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the chunk',
                0
            ),
            array(
                'snippet',
                null,
                InputOption::VALUE_REQUIRED,
                'The content of the chunk',
                ''
            ),
            array(
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the chunk is locked (1 or 0)',
                0
            ),
            array(
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the chunk is static (1 or 0)',
                0
            ),
            array(
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the chunk',
                ''
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the name to the properties
        $properties['name'] = $this->argument('name');

        // Add options to the properties
        $optionKeys = array(
            'description', 'category', 'snippet', 'locked', 'static', 'static_file'
        );

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Chunk created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Chunk ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to create chunk');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
