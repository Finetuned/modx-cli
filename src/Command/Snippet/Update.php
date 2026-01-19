<?php

namespace MODX\CLI\Command\Snippet;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a MODX snippet
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Element\Snippet\Update';
    protected $required = ['id'];

    protected $name = 'snippet:update';
    protected $description = 'Update a MODX snippet';

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
                'The ID of the snippet to update'
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
                'The name of the snippet'
            ],
            [
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the snippet'
            ],
            [
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the snippet'
            ],
            [
                'snippet',
                null,
                InputOption::VALUE_REQUIRED,
                'The PHP code of the snippet'
            ],
            [
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the snippet is locked (1 or 0)'
            ],
            [
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the snippet is static (1 or 0)'
            ],
            [
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the snippet'
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
        // Get the snippet ID from arguments
        $snippetId = (int) $this->argument('id');

        // Pre-populate properties with existing snippet data to avoid requiring name parameter
        if (!$this->prePopulateFromExisting($properties, \MODX\Revolution\modSnippet::class, $snippetId)) {
            $this->error("Snippet with ID {$snippetId} not found");
            return false;
        }

        // Add options to the properties with type conversion
        $optionKeys = [
            'name', 'description', 'category', 'snippet', 'properties', 'static_file'
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
            $this->info('Snippet updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Snippet ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to update snippet');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
