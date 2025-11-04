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
    protected $processor = 'Element\Chunk\Update';
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
        // Get the chunk ID from arguments
        $chunkId = $this->argument('id');
        
        // Pre-populate properties with existing chunk data to avoid requiring name parameter
        if (!$this->prePopulateFromExisting($properties, 'modChunk', $chunkId)) {
            $this->error("Chunk with ID {$chunkId} not found");
            return false;
        }

        // Add options to the properties with type conversion
        $optionKeys = array(
            'name', 'description', 'category', 'snippet', 'static_file'
        );
        
        $typeMap = array(
            'category' => 'int',
            'locked' => 'bool',
            'static' => 'bool'
        );

        $this->addOptionsToProperties($properties, $optionKeys, $typeMap);
        
        // Handle locked and static separately since they need special handling
        if ($this->option('locked') !== null) {
            $properties['locked'] = (int) filter_var($this->option('locked'), FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($this->option('static') !== null) {
            $properties['static'] = (int) filter_var($this->option('static'), FILTER_VALIDATE_BOOLEAN);
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json') || $this->option('format') === 'json') {
            return parent::processResponse($response);
        }

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
