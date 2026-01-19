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
    protected $required = ['id'];

    protected $name = 'chunk:update';
    protected $description = 'Update a MODX chunk';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'id',
                InputArgument::REQUIRED,
                'The ID of the chunk to update'
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
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the chunk'
            ],
            [
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the chunk'
            ],
            [
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the chunk'
            ],
            [
                'snippet',
                null,
                InputOption::VALUE_REQUIRED,
                'The content of the chunk'
            ],
            [
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the chunk is locked (1 or 0)'
            ],
            [
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the chunk is static (1 or 0)'
            ],
            [
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the chunk'
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Get the chunk ID from arguments
        $chunkId = (int) $this->argument('id');

        // Pre-populate properties with existing chunk data to avoid requiring name parameter
        if (!$this->prePopulateFromExisting($properties, \MODX\Revolution\modChunk::class, $chunkId)) {
            $this->error("Chunk with ID {$chunkId} not found");
            return false;
        }

        // Add options to the properties with type conversion
        $optionKeys = [
            'name', 'description', 'category', 'snippet', 'static_file'
        ];

        $typeMap = [
            'category' => 'int',
            'locked' => 'bool',
            'static' => 'bool'
        ];

        $this->addOptionsToProperties($properties, $optionKeys, $typeMap);

        // Handle locked and static separately since they need special handling
        if ($this->option('locked') !== null) {
            $properties['locked'] = (int) filter_var($this->option('locked'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->option('static') !== null) {
            $properties['static'] = (int) filter_var($this->option('static'), FILTER_VALIDATE_BOOLEAN);
        }
        return null;
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
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Chunk updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Chunk ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to update chunk');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
