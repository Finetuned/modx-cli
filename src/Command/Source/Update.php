<?php

namespace MODX\CLI\Command\Source;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a MODX media source
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Source\Update';

    protected $name = 'source:update';
    protected $description = 'Update a MODX media source';

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
                'The source ID'
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
                'The name of the media source'
            ],
            [
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the media source'
            ],
            [
                'class_key',
                null,
                InputOption::VALUE_REQUIRED,
                'The class key of the media source'
            ],
            [
                'source-properties',
                null,
                InputOption::VALUE_REQUIRED,
                'The properties of the media source (JSON format)'
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
        // Add the ID to the properties
        $properties['id'] = $this->argument('id');

        // Pre-populate from existing source
        $this->prePopulateFromExisting($properties, 'Source\Get', 'id');

        // Add options to the properties
        $optionKeys = ['name', 'description', 'class_key'];

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }

        // Handle source-properties separately (maps to 'properties' in MODX)
        if ($this->option('source-properties') !== null) {
            $properties['properties'] = $this->option('source-properties');
        }
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
            $this->info('Media source updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Source ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to update media source');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
