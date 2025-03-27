<?php

namespace MODX\CLI\Command\Chunk;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a MODX chunk
 */
class Update extends ProcessorCmd
{
    protected $processor = 'element/chunk/update';
    protected $required = array('id');

    protected $name = 'chunk:update';
    protected $description = 'Update a MODX chunk';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the chunk to update'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the chunk'
            ),
            array(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the chunk'
            ),
            array(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the chunk'
            ),
            array(
                'snippet',
                null,
                InputOption::VALUE_REQUIRED,
                'The content of the chunk'
            ),
            array(
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the chunk is locked (1 or 0)'
            ),
            array(
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the chunk is static (1 or 0)'
            ),
            array(
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the chunk'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add options to the properties
        $optionKeys = array(
            'name', 'description', 'category', 'snippet', 'locked', 'static', 'static_file'
        );

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Chunk updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Chunk ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to update chunk');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
